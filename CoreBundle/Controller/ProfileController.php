<?php

namespace CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use CoreBundle\Form\ProfileType;
use CoreBundle\Form\ProfileUserType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use CoreBundle\Form\PasswordType;
use CoreBundle\Entity\Actor;
use CoreBundle\Entity\Image;
//use EcommerceBundle\Entity\Address;
//use EcommerceBundle\Form\AddressType;

 
class ProfileController extends Controller
{
    
    /**
     * Profile details
     *
     * @Route("/profile")
     * @Method("GET")
     * @Template("CoreBundle:Profile:index.html.twig")
     * 
     */
    public function indexAction()
    {
        
        $em = $this->container->get('doctrine')->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
   
        if($user->isGranted('ROLE_ADMIN')) {
            return $this->redirect( $this->generateUrl('core_optic_dashboard'));
        }
        $form_pass = $this->createForm(new PasswordType(), $user);
        
        if($user->isGranted('ROLE_USER')) {
            
            //edit user 
            $form = $this->createForm(new ProfileUserType(), $user);
            
            //billing 
            //$address = $checkoutManager->getBillingAddress($this->container->get('security.context'));
            //$billingForm = $this->createForm(new AddressType($this->container->get('security.context')), $address);

            //delivery
            //$addresses = $em->getRepository('EcommerceBundle:Address')
            //->findBy(array(
            //        'actor' => $user,
            //    ));
            //$formDelivery = $this->newDeliveryAction($this->get('request'));
            ////transaction
            //$transactions = $em->getRepository('EcommerceBundle:Transaction')->findAllFinished($user);
            
            return $this->render('CoreBundle:Profile:index_.html.twig', array(
                'form' => $form->createView(),
                'form_pass' => $form_pass->createView(),
//                'billingForm' => $billingForm->createView(),
//                'addresses' => $addresses,
//                'formDelivery' => $formDelivery['form'],
//                'transactions' => $transactions,
//                'adverts' => $adverts
            ));
        }
        
        $form = $this->createForm(new ProfileType(), $user);
        
        
        return array(
            'form' => $form->createView(),
            'form_pass' => $form_pass->createView(),
        );
    }
    
 
    
    /**
     * Profile details
     *
     * @Route("/profile/edit")
     * @Method({"GET","POST"})
     */
    public function editAction(Request $request)
    {
        $redirect = '';
        if($request->query->get('redirect') != '') {
            $redirect = '?redirect='.$request->query->get('redirect');
        }
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        //User edit
        if($user->isGranted('ROLE_USER')) {
            $form = $this->createForm(new ProfileUserType(), $user);
            if ('POST' === $request->getMethod()) {
                $form->bind($request);
                if ($form->isValid()) {
                    $em->persist($user);
                    $em->flush();
                    $this->container->get('session')->getFlashBag()->add('success', 'profile.saved');
                }
            }
            $url = $this->container->get('router')->generate('core_profile_index');
            return new RedirectResponse($url);
        }

        //optic edit
        $form = $this->createForm(new ProfileType(), $user);
        if ('POST' === $request->getMethod()) {
            $form->bind($request);
            if ($form->isValid()) {
                $em->persist($user);
                $em->flush();
                if($redirect!='') return $this->redirect($request->query->get('redirect'));
                $this->container->get('session')->getFlashBag()->add('success', 'profile.saved');
            }
        }

        $url = $this->container->get('router')->generate('core_profile_index').$redirect;
        return new RedirectResponse($url);
    }

       /**
     * Profile details
     *
     * @Route("/profile/{id}")
     * @Method("GET")
     * @Template("FrontBundle:Profile:show.html.twig")
     * 
     */
    public function showAction($id)
    {
        $em = $this->container->get('doctrine')->getManager();
        $user = $em->getRepository('CoreBundle:Optic')->find($id);
        
        //stats
        $this->get('front_manager')->setStats($user);
        
        $products = $em->getRepository('EcommerceBundle:Product')->findByOptic($user);
        
        if (!is_object($user)) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        //service
        $category = $em->getRepository('EcommerceBundle:Category')->findOneBySlug('servicios-opticos');
        $services = $em->getRepository('EcommerceBundle:Product')->findBy(array('optic' => $user, 'category' => $category ));
        
        return array(
            'id' => $id,
            'user' => $user,
            'products' => $products,
            'services' => $services
        );
    }
    
    /**
     * Edit the billing address
     *
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/profile/billing/")
     * @Method({"GET","POST"})
     * @Template("EcommerceBundle:Profile:Billing/edit.html.twig")
     * 
     */
    public function editBillingAction(Request $request)
    {
        $em = $this->container->get('doctrine')->getManager();
        $checkoutManager = $this->container->get('checkout_manager');


        /** @var Address $address */
        $address = $checkoutManager->getBillingAddress($this->container->get('security.context'));
        $form = $this->createForm(new AddressType($this->container->get('security.context')), $address);

        if ('POST' === $request->getMethod()) {
            
            $form->bind($request);

            if ($form->isValid()) {
                $em->persist($address);
                $em->flush();

                $url = $this->container->get('router')->generate('core_profile_index').'?billing=1';
                $this->container->get('session')->getFlashBag()->add('success', 'account.address.saved');
                return new RedirectResponse($url);
            }
        }

        return array(
                    'form' => $form->createView()
                );
    }

    /**
     * Set the address as the billing address
     *
     * @param integer $id
     *
     * @throws AccessDeniedException
     * @return RedirectResponse
     * 
     * @Route("/profile/delivery/{id}/set-for-billing")
     * @Method("GET")
     * @Template("EcommerceBundle:Profile:Delivery/show.html.twig")
     */
    public function setBillingAddressAction($id)
    {
        $em = $this->container->get('doctrine')->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $address = $em->getRepository('EcommerceBundle:Address')
            ->findOneBy(array(
                    'id'   => $id,
                    'actor' => $user,
                ));
        if (is_null($address)) {
            throw new AccessDeniedException();
        }

        $em->getRepository('EcommerceBundle:Address')->removeForBillingToAllAddresses($user->getId());

        $address->setForBilling(1);
//        $em->persist($address);
        $em->flush();

        $url = $this->container->get('router')->generate('core_profile_index').'?delivery=1';

        $this->container->get('session')->getFlashBag()->add('success', 'account.address.assigned.for.billing');

        return new RedirectResponse($url);
    }

    /**
     * Show delivery addresses
     *
     * @return Response
     * 
     * @Route("/profile/delivery/")
     * @Method({"GET","POST"})
     * @Template("EcommerceBundle:Profile:Delivery/show.html.twig")
     */
    public function showDeliveryAction()
    {
        $em = $this->container->get('doctrine')->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $addresses = $em->getRepository('EcommerceBundle:Address')
            ->findBy(array(
                    'actor' => $user,
                ));

        return array(
                'user'      => $user,
                'addresses' => $addresses
            );
    }

    /**
     * Add delivery address
     *
     * @param Request $request
     * @return Response
     * 
     * @Route("/profile/delivery/new")
     * @Method({"GET","POST"})
     * @Template("EcommerceBundle:Profile:Delivery/new.html.twig")
     */
    public function newDeliveryAction(Request $request)
    {
        $em = $this->container->get('doctrine')->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $country = $em->getRepository('CoreBundle:Country')->find('es');
        $address = new Address();
        $address->setForBilling(false);
        $address->setCountry($country);
        $address->setActor($user);
        /** @var Address $address */
        $form = $this->createForm(new AddressType($this->container->get('security.context')), $address);

        if ('POST' === $request->getMethod()) {
            $form->bind($request);

            if ($form->isValid()) {
                $em->persist($address);
                $em->flush();

                $url = $this->container->get('router')->generate('core_profile_index').'?delivery=1';
                $this->container->get('session')->getFlashBag()->add('success', 'account.address.added');
                return new RedirectResponse($url);
            }
        }

        return array(
                    'form' => $form->createView()
                );
    }

    /**
     * Edit delivery addresses
     *
     * @param Request $request
     * @param integer $id
     *
     * @throws AccessDeniedException
     * @return Response
     * 
     * @Route("/profile/delivery/{id}/edit")
     * @Method({"GET","POST"})
     * @Template("EcommerceBundle:Profile:Delivery/edit.html.twig")
     */
    public function editDeliveryAction(Request $request, $id)
    {
        $em = $this->container->get('doctrine')->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $address = $em->getRepository('EcommerceBundle:Address')
            ->findOneBy(array(
                    'id'   => $id,
                    'actor' => $user,
                ));
        if (is_null($address)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new AddressType($this->container->get('security.context')), $address);

        if ('POST' === $request->getMethod()) {
            $form->bind($request);

            if ($form->isValid()) {
                $em->persist($address);
                $em->flush();

                $url = $this->container->get('router')->generate('core_profile_index').'?delivery=1';
                $this->container->get('session')->getFlashBag()->add('success', 'account.address.saved');
                return new RedirectResponse($url);
            }
        }

        return array(
                    'form'    => $form->createView(),
                    'address' => $address
                );
    }

    /**
     * Delete delivery addresses
     *
     * @param integer $id
     *
     * @throws AccessDeniedException
     * @return RedirectResponse
     * 
     * @Route("/profile/delivery/{id}/delete")
     * @Method({"GET","POST"})
     * @Template("EcommerceBundle:Profile:Delivery/edit.html.twig")
     */
    public function deleteDeliveryAction($id)
    {
        $em = $this->container->get('doctrine')->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $address = $em->getRepository('EcommerceBundle:Address')
            ->findOneBy(array(
                    'id'   => $id,
                    'actor' => $user,
                ));
        if (is_null($address)) {
            throw new AccessDeniedException();
        }

        $em->remove($address);
        $em->flush();

        $url = $this->container->get('router')->generate('core_profile_index').'?delivery=1';
        $this->container->get('session')->getFlashBag()->add('success', 'account.address.deleted');
        return new RedirectResponse($url);
    }
    
    /**
     * Show transactions list
     *
     * @return Response
     * 
     * @Route("/profile/transaction/")
     * @Method({"GET","POST"})
     * @Template("FrontBundle:Profile:Transaction/show.html.twig")
     */
    public function showTransactionAction()
    {
        $em = $this->container->get('doctrine')->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $transactions = $em->getRepository('EcommerceBundle:Transaction')->findAllFinished($user);

        return array(
                    'transactions' => $transactions        
                );
    }
    
    /**
     * Show invoice
     *
     * @param Request $request
     * @param string  $number
     *
     * @throws AccessDeniedException
     * @return Response
     * 
     * @Route("/profile/invoice/{number}/view")
     * @Method("GET")
     * @Template("EcommerceBundle:Profile:Invoice/show.html.twig")
     */
    public function showInvoiceAction(Request $request, $number)
    {
        
        $em = $this->container->get('doctrine')->getManager();
        /** @var Invoice $invoice */
        $invoice = $em->getRepository('EcommerceBundle:Invoice')->findOneBy(array(
            'invoiceNumber' => $number
        ));

        if (!$invoice ||
            false === $this->container->get('checkout_manager')->isCurrentUserOwner($invoice->getTransaction())) {
            throw new AccessDeniedException();
        }

        /** @var CheckoutManager $checkoutManager */
        $checkoutManager = $this->container->get('checkout_manager');

        $delivery = $invoice->getTransaction()->getDelivery();
        $totals = $checkoutManager->calculateTotals($invoice->getTransaction(), $delivery);

        // download invoice
        if ('true' === $request->get('download')) {
            $html = $this->container->get('templating')->render('EcommerceBundle:Profile:Invoice/download.html.twig', array(
                    'delivery' => $delivery,
                    'invoice'  => $invoice,
                    'totals'   => $totals,
                ));
            $html2pdf = $this->get('html2pdf_factory')->create();
            $html2pdf->WriteHTML($html);
            
            return new Response(
                $html2pdf->Output('invoice'.$invoice->getInvoiceNumber().'.pdf'),
                200,
                array(
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="invoice'.$invoice->getInvoiceNumber().'.pdf"'
                )
            );
        }

        return array(
                'delivery' => $delivery,
                'invoice'  => $invoice,
                'totals'   => $totals,
            );
    }
    
    /**
     * Upload profile image
     * @Route("/user/{id}/upload", name="upload_profile_image", defaults={"_format" = "json"})
    */
    public function uploadProfileImage(Request $request, $id)
    {

        if (!$id) {
            throw $this->createNotFoundException('Unable to upload.');
        }

        if ($id != $this->get('security.context')->getToken()->getUser()->getId()) {
            throw $this->createNotFoundException('Can not upload anything to a profile that is not their own.');
        }
        
        $em = $this->container->get('doctrine')->getManager();
        $entity = $this->get('security.context')->getToken()->getUser();
        if ($request->files->get('file') instanceof UploadedFile) {
            $imagePath = $this->get('core_manager')->uploadProfileImagePost($request->files->get('file'), $entity);
            $img = new Image();
            $img->setPath($imagePath);
            $em->persist($img);
            $entity->setImage($img);
            $em->flush();
        }

        return new JsonResponse($imagePath);

    }
    
    /**
     * Upload profile image
     * @Route("/invest/{id}/upload", name="upload_invest_doc", defaults={"_format" = "json"})
    */
    public function upload(Request $request, $id)
    {

        if (!$id) {
            throw $this->createNotFoundException('Unable to upload.');
        }

//        if ($id != $this->get('security.context')->getToken()->getUser()->getId()) {
//            throw $this->createNotFoundException('Can not upload anything to a profile that is not their own.');
//        }

        return new JsonResponse(
                $this->uploadInvestDoc($request, $id)
                );

    }


    /**
     * Profile details
     *
     * @Route("/profile/change-password")
     * @Method("POST")
     */
    public function changePasswordAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form_pass = $this->createForm(new PasswordType(), $user);

        if ($request->isMethod('POST')) {
            $form_pass->bind($request);

            if ($form_pass->isValid()) {
                $data = $request->get('password');
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder(new Actor());
                $encodePasswordOld = $encoder->encodePassword($data['password_old'], $user->getSalt());
                $actor = $em->getRepository('CoreBundle:Actor')->findOneByPassword($encodePasswordOld);
                
                if (!$actor instanceof Actor) {
                    throw $this->createNotFoundException('This user does not found.');
                }
                
                if($actor->getId().' == '.$user->getId() && $data['password']['first']  ==  $data['password']['second']){
                    $encodePassword = $encoder->encodePassword($data['password']['first'], $user->getSalt());
                    $user->setPassword($encodePassword);
                    $em->flush();
                    $this->container->get('session')->getFlashBag()->add('success', 'account.password.saved');
                }else{
                    $this->container->get('session')->getFlashBag()->add('warning', 'account.password.error');
                }
            }
        }
        
        $url = $this->container->get('router')->generate('core_profile_index');
        return new RedirectResponse($url);

    }
    
    /**
     * Profile details
     *
     * @Route("/profile/transaction/binding-agreement-investment")
     * @Method("GET")
     */
    public function downloadBindingAgreementInvestmentAction() 
    {
        
 
        $html = $this->container->get('templating')->render('FrontBundle:Profile:Transaction/document/binding.agreement.investment.html.twig', 
                array()
                );
        $html2pdf = $this->get('html2pdf_factory')->create();
        $html2pdf->WriteHTML($html);

       
        return new Response(
            $html2pdf->Output('binding-agreement-investment.pdf'),
            200,
            array(
                'Content-Type'        => 'application/pdf',
                //'Content-Disposition' => 'attachment; filename="binding-agreement-investment.pdf"'
            )
        );
        
        
    }
}
