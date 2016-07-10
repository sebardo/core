<?php
namespace CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CoreBundle\Form\RegistrationType;
use CoreBundle\Form\Model\Registration;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use CoreBundle\Form\ActorType;
use CoreBundle\Form\ActorEditType;
use CoreBundle\Entity\Actor;
use CoreBundle\Entity\Role;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use CoreBundle\Entity\Image;
use EcommerceBundle\Entity\Address;
use CoreBundle\Entity\BaseActor;
use stdClass;
use Symfony\Component\HttpFoundation\Response;
use CoreBundle\Form\RecoveryPasswordType;
use CoreBundle\Form\EmailType as ActorEmailType;
use CoreBundle\Entity\Optic;
use CoreBundle\Entity\NewsletterShipping;
use CoreBundle\Entity\Newsletter;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

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
     * Creates a new Team entity.
     *
     * @param Request $request The request
     *
     * @return array|RedirectResponse
     *
     * @Route("/admin/actor/")
     * @Method("POST")
     * @Template("CoreBundle:Actor:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Actor();
        $form = $this->createForm(new ActorType(), $entity);
         
        $form->handleRequest($request);

        if ($form->isValid()) 
        {
            $em = $this->getDoctrine()->getManager();

            //crypt password
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder(new Actor());
            $encodePassword = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
            $entity->setPassword($encodePassword);
            $role = $em->getRepository('CoreBundle:Role')->findOneByRole(Role::USER);
            $entity->addRole($role);
            $image = $form->getNormData()->getImage();
            $entity->setImage(null);
            $em->persist($entity);
            $em->flush();

            if ($image instanceof UploadedFile) {
                $imagePath = $this->get('admin_manager')->uploadProfileImage($image, $entity);
                $img = new Image();
                $img->setPath($imagePath);
                $em->persist($img);
                $entity->setImage($img);
                $em->flush();
            }

            
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'actor.created');

            //if come from popup
            if($request->query->get('referer') != '') {
                
                $url = null;
                $x = 1;
                foreach ($request->query->all() as $key => $value) {
                        if($key == 'referer') $url .= $value.'?';
                        else $url .= $key.'='.$value;
                        if(count($request->query->all()) != $x && $request->query->all() != 1) $url .= '&';
                        $x++;
                }
                return $this->redirect($url.'&addUser='.$entity->getId());
            }
            
            return $this->redirect($this->generateUrl('core_actor_show', array('id' => $entity->getId())));
        }else{
//             die('invalid');
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

  
    
    /**
     * Displays a form to create a new Team entity.
     *
     * @return array
     *
     * @Route("/admin/actor/new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Actor();
        $form = $this->createForm(new ActorType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    
    /**
     * Finds and displays a Actor entity.
     *
     * @param int $id The entity id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/admin/actor/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Actor $entity */
        $entity = $em->getRepository('CoreBundle:Actor')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Actor entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        
        $shippingForm = $this->createForm(new ActorEmailType(array('email' => $entity->getEmail())));
        
        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            'shippingForm' => $shippingForm->createView()
        );
    }

    /**
     * Displays a form to edit an existing Actor entity.
     *
     * @param int $id The entity id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/admin/actor/{id}/edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Actor $entity */
        $entity = $em->getRepository('CoreBundle:Actor')->find($id);
        $entity_image = clone $entity;
        $entity_image->setImage(null);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Actor entity.');
        }

        $editForm = $this->createForm(new ActorEditType(), $entity_image);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Actor entity.
     *
     * @param Request $request The request
     * @param int     $id      The entity id
     *
     * @throws NotFoundHttpException
     * @return array|RedirectResponse
     *
     * @Route("/admin/actor/{id}")
     * @Method("PUT")
     * @Template("CoreBundle:Actor:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Actor $entity */
        $entity = $em->getRepository('CoreBundle:Actor')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Actor entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new ActorEditType(), $entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            
            $password = $editForm->getNormData()->getPassword();
            if($password != ''){
                 //crypt password
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder(new Actor());
                $encodePassword = $encoder->encodePassword($password, $entity->getSalt());
                $entity->setPassword($encodePassword);
            }
            
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'user.edited');

            return $this->redirect($this->generateUrl('core_actor_show', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Actor entity.
     *
     * @param Request $request The request
     * @param int     $id      The entity id
     *
     * @throws NotFoundHttpException
     * @return RedirectResponse
     *
     * @Route("/admin/actor/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /** @var Actor $entity */
            $entity = $em->getRepository('CoreBundle:Actor')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Actor entity.');
            }

            $em->remove($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', 'user.deleted');
        }

        return $this->redirect($this->generateUrl('core_actor_index'));
    }

    /**
     * Creates a form to delete a Actor entity by id.
     *
     * @param int $id The entity id
     *
     * @return Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', HiddenType::class)
            ->getForm();
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
     * @Method("GET")
     * @Template("FrontBundle:Registration:register.html.twig")
     */
    public function registerAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY') ) {
                return $this->redirect($this->get('router')->generate('index'));
        }

        $form = $this->createForm(new RegistrationType(), new Registration());

        return array('form' => $form->createView());
    }
    
    public function getRefererPath(Request $request=null)
    {
        $referer = $request->headers->get('referer');

        $baseUrl = $request->getSchemeAndHttpHost();

        $lastPath = substr($referer, strpos($referer, $baseUrl) + strlen($baseUrl));

        return $lastPath;
    }

    /**
     * Creates
     *
     * @Route("/register/", name="create_actor")
     * @Method("POST")
     * @Template("FrontBundle:Registration:register.html.twig")
     */
    public function createActorAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new RegistrationType(), new Registration());
        $referer = $this->getRefererPath($this->getRequest());
        $form->handleRequest($request);

        if ($form->isValid()) {
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
            $address = new Address();
            $address->setActor($registration->getActor());
            $address->setCity($registration->getCity());
            $address->setState($registration->getState());
            $address->setCountry($registration->getCountry());
            $address->setForBilling(false);
            
            $em->persist($address);
            $em->persist($registration->getActor());
            $em->flush();
            
            //Login
            $username = $registration->getActor()->getName();
            $password = $registration->getActor()->getPassword();
            $email = $registration->getActor()->getEmail();

            //Automatic login
            $token = new UsernamePasswordToken(
               $registration->getActor(),
               $password,
               'secured_area',
               $registration->getActor()->getRoles()
               );

            $this->get('security.token_storage')->setToken($token);

            $this->get('core.mailer')->sendRegisteredEmailMessage($registration->getActor());

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


        }else{
            $string = var_export($this->getErrorMessages($form), true);
            if ($request->isXmlHttpRequest()) {
                $result = array('success' => true, 'message' => $string);
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
        }

        return array('form' => $form->createView());

   }
   
   /**
     * Creates
     *
     * @Route("/register/brand", name="create_actor_brand")
     * @Method("POST")
     * @Template("FrontBundle:Registration:register.html.twig")
     */
    public function createActorBrandAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new RegistrationBrandType(), new RegistrationBrand());
        $referer = $this->getRefererPath($this->getRequest());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $registration = $form->getData();

            //set username
            $registration->getActor()->setUsername($registration->getActor()->getEmail());
                    
            //Encode pass
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($registration->getActor());
            $password = $encoder->encodePassword($registration->getActor()->getPassword(), $registration->getActor()->getSalt());
            $registration->getActor()->setPassword($password);

            //Add ROLE
            $role = $em->getRepository('CoreBundle:Role')->findOneBy(array('role' => 'ROLE_USER'));
            $role2 = $em->getRepository('CoreBundle:Role')->findOneBy(array('role' => 'ROLE_BRAND'));
            $registration->getActor()->addRole($role);
            $registration->getActor()->addRole($role2);
            
            //create address
            $address = new Address();
            $address->setActor($registration->getActor());
            $address->setCity($registration->getCity());
            $address->setState($registration->getState());
            $address->setCountry($registration->getCountry());
            $address->setForBilling(false);
            
            $em->persist($address);
            $em->persist($registration->getActor());
            $em->flush();
            
            //Login
            $username = $registration->getActor()->getName();
            $password = $registration->getActor()->getPassword();
            $email = $registration->getActor()->getEmail();

            //Automatic login
            $token = new UsernamePasswordToken(
               $registration->getActor(),
               $password,
               'secured_area',
               $registration->getActor()->getRoles()
               );

            $this->get('security.token_storage')->setToken($token);

            $this->get('core.mailer')->sendRegisteredEmailMessage($registration->getActor());

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


        }else{
            $string = var_export($this->getErrorMessages($form), true);
            if ($request->isXmlHttpRequest()) {
                $result = array('success' => false, 'message' => $string);
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
        }

        return array('form' => $form->createView());

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
            $user = $em->getRepository('CoreBundle:Optic')->findOneByEmail($email);
            if ($user instanceof Optic) {
                $this->get('core.mailer')->sendRecoveryPasswordMessage($user);
                
                $returnValues->status = 'success';
                $returnValues->message = $this->get('translator')->trans('account.password.recovery.email.success');

            }else{
                $returnValues->status = 'error';
                $returnValues->message = 'Unable to find user.';
            }
            
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
            $user = $em->getRepository('CoreBundle:Optic')->findOneByEmail($email);
            if (!$user) {
               throw $this->createNotFoundException('Unable to find user.');
            }
        }

        if (($user instanceof Actor || $user instanceof Optic) && ($user->getSalt() == $hash)) {
            //Default hash value
            $options = array('hash'=>$hash);
            $form = $this->createForm(new RecoveryPasswordType($options));
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
            
            //Get params
            $recovery = $form->getData();
            $newPassword = $recovery['password'];
            $hash = $recovery['hash'];
            
            $user = $em->getRepository('CoreBundle:Actor')->findOneBySalt($hash);
            $encoder = $factory->getEncoder(new Actor());
            
            if (!$user) {
                $user = $em->getRepository('CoreBundle:Optic')->findOneBySalt($hash);
                $encoder = $factory->getEncoder(new Optic());
                if (!$user) {
                   throw $this->createNotFoundException('Unable to find user.');
                }
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
    
}
