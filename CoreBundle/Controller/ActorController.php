<?php
namespace CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CoreBundle\Form\Model\Registration;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use CoreBundle\Entity\Actor;
use CoreBundle\Entity\Role;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use CoreBundle\Entity\BaseActor;
use Symfony\Component\HttpFoundation\Response;
use CoreBundle\Form\RecoveryPasswordType;
use CoreBundle\Form\EmailType as ActorEmailType;
use CoreBundle\Entity\NewsletterShipping;
use CoreBundle\Entity\Newsletter;
use stdClass;

class ActorController  extends Controller
{
    /**
     * Lists all Actor entities.
     *
     * @return array
     *
     * @Route("/admin/actor")
     * @Method("GET")
     * @Template("CoreBundle:Actor:index.html.twig")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Returns a list of Actor entities in JSON format.
     *
     * @return JsonResponse
     *
     * @Route("/admin/actor/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listJsonAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \Kitchenit\AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('CoreBundle:Actor'));
        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Creates a new Actor entity.
     *
     * @Route("/admin/actor/new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $actor = new Actor();
        $form = $this->createForm('CoreBundle\Form\ActorType', $actor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            //crypt password
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder(new Actor());
            $encodePassword = $encoder->encodePassword($actor->getPassword(), $actor->getSalt());
            $actor->setPassword($encodePassword);
            $role = $em->getRepository('CoreBundle:Role')->findOneByRole(Role::USER);
            $actor->addRole($role);
            $em->persist($actor);
            $em->flush();

            $filesData = $request->files->get('actor');
            if (isset($filesData['image']['file']) && $filesData['image']['file'] instanceof UploadedFile) {            
                $this->get('core_manager')->uploadProfileImage($actor);
            }
            
            $this->get('session')->getFlashBag()->add('success', 'actor.created');
            
            return $this->redirectToRoute('core_actor_show', array('id' => $actor->getId()));
        }

        return array(
            'entity' => $actor,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Actor entity.
     *
     * @Route("/admin/actor/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Actor $actor)
    {
        $deleteForm = $this->createDeleteForm($actor);
        $shippingForm = $this->createForm('CoreBundle\Form\EmailType', null, array('email' => $actor->getEmail()));

        return array(
            'entity' => $actor,
            'delete_form' => $deleteForm->createView(),
            'shippingForm' => $shippingForm->createView()
        );
    }
    
   /**
     * Displays a form to edit an existing Actor entity.
     *
     * @Route("/admin/actor/{id}/edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Actor $actor)
    {
        $oldPassword = $actor->getPassword();
        $deleteForm = $this->createDeleteForm($actor);
        $editForm = $this->createForm('CoreBundle\Form\ActorEditType', $actor);
        $editForm->handleRequest($request);
        
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            //crypt password
            $password = $editForm->getNormData()->getPassword();
            if($password != ''){
                 
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder(new Actor());
                $encodePassword = $encoder->encodePassword($password, $actor->getSalt());
                $actor->setPassword($encodePassword);
            }else{
                $actor->setPassword($oldPassword);
            }
            $em->persist($actor);
            $em->flush();
            
            //image
            $filesData = $request->files->get('actor_edit');
            if (isset($filesData['image']['file']) && $filesData['image']['file'] instanceof UploadedFile) {            
                $this->get('core_manager')->uploadProfileImage($actor);
            }

            $this->get('session')->getFlashBag()->add('success', 'actor.edited');
            
            return $this->redirectToRoute('core_actor_show', array('id' => $actor->getId()));
        }

        return array(
            'entity' => $actor,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Actor entity.
     *
     * @Route("/admin/actor/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Actor $actor)
    {
        $form = $this->createDeleteForm($actor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($actor);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('info', 'actor.deleted');
        }

        return $this->redirectToRoute('core_actor_index');
    }

    /**
     * Creates a form to delete a Actor entity.
     *
     * @param Actor $actor The Actor entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Actor $actor)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('core_actor_delete', array('id' => $actor->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    /**
     *
     * REGISTRATION
     *
     */

    /**
     * Create new Actor entity.
     *
     * @Route("/register", name="register")
     * @Method({"GET", "POST"})
     * @Template("CoreBundle:Registration:register.html.twig")
     */
    public function registerAction(Request $request)
    {
        
        $registration = new Registration();
        $form = $this->createForm('CoreBundle\Form\RegistrationType', $registration);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $registration = $form->getData();

            //Encode pass
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($registration->getActor());
            $password = $encoder->encodePassword($registration->getActor()->getPassword(), $registration->getActor()->getSalt());
            $registration->getActor()->setPassword($password);

            //Add ROLE
            $role = $em->getRepository('CoreBundle:Role')->findOneBy(array('role' => 'ROLE_USER'));
            $registration->getActor()->addRole($role);
            
            //create address
            //$address = new Address();
            //$address->setActor($registration->getActor());
            //$address->setCity($registration->getCity());
            //$address->setState($registration->getState());
            //$address->setCountry($registration->getCountry());
            //$address->setForBilling(false);
            //
            //$em->persist($address);
            $em->persist($registration->getActor());
            $em->flush();
            
            //Login
            $token = new UsernamePasswordToken(
               $registration->getActor(),
               $password,
               'secured_area',
               $registration->getActor()->getRoles()
               );

            $this->get('security.token_storage')->setToken($token);

            $this->get('core.mailer')->sendRegisteredEmailMessage($registration->getActor());

            $referer = $this->getRefererPath($request);
            if ($referer == '/identification') {
                return $this->redirect($this->generateUrl('ecommerce_checkout_detail'));
            }
   
            if ($request->isXmlHttpRequest()) {
                $result = array('success' => true);
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            return $this->redirect($this->generateUrl('core_profile_index'));


        }
       
        
        return array(
            'form' => $form->createView()
            );
    }
 
   
    public function getRefererPath(Request $request=null)
    {
        $referer = $request->headers->get('referer');
        $baseUrl = $request->getSchemeAndHttpHost();
        $lastPath = substr($referer, strpos($referer, $baseUrl) + strlen($baseUrl));

        return $lastPath;
    }
    
   /**
     * Validate registration.
     *
     * @Route("/validate/{email}/{hash}", name="register_validate")
     * @Method("GET")
     * @Template("FrontBundle:Registration:validate.html.twig")
     */
    public function validateAction($email, $hash)
    {

        $em = $this->getDoctrine()
                    ->getManager();

        $user = $em->getRepository('CoreBundle:BaseActor')->findOneByEmail($email);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find actor.');
        }

        if (($user instanceof BaseActor) && ($user->getSalt() == $hash)) {
            //check validate time
             $now = new \DateTime();
             $diff = $now->getTimestamp() - $user->getCreated()->getTimestamp();
             $core = $this->container->getParameter('core');
             if ($diff < $core['validate_time']) {
                 $user->setIsActive(true);
                 $em->persist($user);
                 $em->flush();

                 $this->get('core.mailer')->sendValidateEmailMessage($user);

             }
        }

    }
  
    /**
     *
     * RECOVERY PASSWORD
     */

    /**
     * Validate registration.
     *
     * @Route("/recovery")
     * @Template("CoreBundle:RecoveryPassword:recovery.html.twig")
     */
    public function recoveryAction()
    {
        return array();
    }
    
    /**
     * Validate registration.
     *
     * @Route("/recovery-password/{email}", name="recovery_password", defaults={"email" = ""})
     * @Method("POST")
     * @Template("CoreBundle:RecoveryPassword:recovery.password.html.twig")
     */
    public function recoveryPasswordAction($email)
    {

        $em = $this->getDoctrine()
                    ->getManager();
        $user = $em->getRepository('CoreBundle:Actor')->findOneByEmail($email);

        $returnValues = new stdClass();

        if ($user instanceof Actor) {
            $this->get('core.mailer')->sendRecoveryPasswordMessage($user);
            
            $returnValues->status = 'success';
            $returnValues->message = $this->get('translator')->trans('account.password.recovery.email.success');

        } else {
            $returnValues->status = 'error';
            $returnValues->message = 'Unable to find user.';
        }

        $response = new Response();
        $response->setContent(json_encode(array(
            'answer' => $returnValues
        )));
        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }

    /**
     * Validate registration.
     *
     * @Route("/recovery-password-form/{email}/{hash}", name="recovery_password_form")
     * @Method("GET")
     * @Template("CoreBundle:RecoveryPassword:recovery.password.html.twig")
     */
    public function recoveryPasswordFormAction($email, $hash)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('CoreBundle:Actor')->findOneByEmail($email);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find user.');
        }

        if ($user instanceof Actor && $user->getSalt() == $hash) {
            //Default hash value
            $options = array('hash'=>$hash);
            $form = $this->createForm(new RecoveryPasswordType(), null, $options);
            return array('form' => $form->createView());
        } else {
            throw $this->createNotFoundException('Invalid parameters. {'.$hash.'}');
        }

    }

    /**
     * Creates
     *
     * @Route("/recovery-password-form/", name="recovery_password_create")
     * @Method("POST")
     * @Template("CoreBundle:RecoveryPassword:recovery.password.html.twig")
     */
    public function recoveryPasswordUpdateAction()
    {

        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new RecoveryPasswordType());
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            
            //Encode pass
            $factory = $this->get('security.encoder_factory');
            $recovery = $form->getData();
            $newPassword = $recovery['password'];
            $hash = $recovery['hash'];
            $user = $em->getRepository('CoreBundle:Actor')->findOneBySalt($hash);
            $encoder = $factory->getEncoder(new Actor());
            
            if (!$user) {
                throw $this->createNotFoundException('Unable to find user.');
            }
            $newSalt = md5(uniqid(null, true));
            $password = $encoder->encodePassword($newPassword, $newSalt);
            $user->setSalt($newSalt);
            $user->setPassword($password);
            $em->flush();
            
            $this->get('core.mailer')->sendRecoveryPasswordConfirmation($user);
            
            return $this->render('CoreBundle:RecoveryPassword:create.html.twig');

        }
        return array('form' => $form->createView());
   }
   
    
   /**
     * Creates a new Newsletter entity.
     *
     * @param Request $request The request
     *
     * @return array|RedirectResponse
     *
     * @Route("/admin/actor/{id}/email")
     * @Template("CoreBundle:Actor:email.html.twig")
     */
    public function emailAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Newsletter $entity */
        $entity = $em->getRepository('CoreBundle:Actor')->find($id);
        
        $form = $this->createForm(new ActorEmailType(array('email' => $entity->getEmail())));
       
        if($request->getMethod() == 'POST'){
            $form->handleRequest($request);

            if ($form->isValid()) {
                //Get params
                $data = $form->getData();
                $email = $data['email'];            
                if($entity->getEmail() == $email){
                    
                    $news = new Newsletter();
                    $news->setTitle($data['subject']);
                    $news->setBody($data['body']);
                    $news->setActive(true);
                    $em->persist($news);
                    
                    $shipping = new NewsletterShipping();
                    $shipping->setNewsletter($news);
                    $shipping->setActor($entity);
                    $shipping->setTotalSent(1);
                    $shipping->setType(NewsletterShipping::TYPE_PERSONAL);
                    $em->persist($shipping);
                    $em->flush();
                    
                    $this->get('core.mailer')->sendActorEmail($email, $data['subject'], $data['body']);
                    
                    //if come from popup
                    if ($request->isXMLHttpRequest()) {         
                        return new JsonResponse(array(
                                    'id' => $shipping->getId()
                                ));
                    }

                    $this->get('session')->getFlashBag()->add('success', 'user.email.created');
                    return $this->redirect($this->generateUrl('core_actor_show', array('id' => $entity->getId())));
                }

            }
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    
    private function getErrorMessages(\Symfony\Component\Form\Form $form) {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }
    
}
