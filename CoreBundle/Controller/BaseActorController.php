<?php
namespace CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CoreBundle\Form\Model\Registration;
use CoreBundle\Form\Model\RegistrationShort;
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
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use CoreBundle\Entity\Image;
use EcommerceBundle\Entity\Address;
use stdClass;

class BaseActorController  extends Controller
{
    /**
     * Lists all BaseActor entities.
     *
     * @return array
     *
     * @Route("/admin/baseactor")
     * @Method("GET")
     * @Template("CoreBundle:BaseActor:index.html.twig")
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
     * @Route("/admin/baseactor/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listJsonAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \Kitchenit\AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('CoreBundle:BaseActor'));
        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Creates a new Actor entity.
     *
     * @Route("/admin/baseactor/new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $actor = new BaseActor();
        $form = $this->createForm('CoreBundle\Form\BaseActorType', $actor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            //crypt password
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder(new BaseActor());
            $encodePassword = $encoder->encodePassword($actor->getPassword(), $actor->getSalt());
            $actor->setPassword($encodePassword);
            $role = $em->getRepository('CoreBundle:Role')->findOneByRole(Role::USER);
            $actor->addRole($role);
            $em->persist($actor);
            $em->flush();

            $filesData = $request->files->get('baseactor');
            if (isset($filesData['image']['file']) && $filesData['image']['file'] instanceof UploadedFile) {            
                $this->get('core_manager')->uploadProfileImage($actor);
            }
            
            $this->get('session')->getFlashBag()->add('success', 'actor.created');
            
            return $this->redirectToRoute('core_baseactor_show', array('id' => $actor->getId()));
        }

        return array(
            'entity' => $actor,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Actor entity.
     *
     * @Route("/admin/baseactor/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction(BaseActor $actor)
    {
        $returnValues = array();
        $deleteForm = $this->createDeleteForm($actor);
        if($this->get('twig.global')->checkUse('CoreExtraBundle')){
            $returnValues['shippingForm'] = $this->createForm('CoreBundle\Form\EmailType', null, array('email' => $actor->getEmail()));
        }
        if($this->get('twig.global')->checkUse('EcommerceBundle')){
            $addressForm = $this->createForm('EcommerceBundle\Form\AddressType', null, array('token_storage' => $this->container->get('security.token_storage')));
            $returnValues['addressForm'] = $addressForm->createView();
        }
        
        return array_merge($returnValues, array(
            'entity' => $actor,
            'delete_form' => $deleteForm->createView()
        ));
    }
    
   /**
     * Displays a form to edit an existing Actor entity.
     *
     * @Route("/admin/baseactor/{id}/edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, BaseActor $actor)
    {
        $oldPassword = $actor->getPassword();
        $deleteForm = $this->createDeleteForm($actor);
        $editForm = $this->createForm('CoreBundle\Form\BaseActorEditType', $actor);
        $editForm->handleRequest($request);
        
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            if($actor->getRemoveImage()){
                $actor->setImage(null);
            }
            
            //crypt password
            $password = $editForm->getNormData()->getPassword();
            if($password != ''){
                 
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder(new BaseActor());
                $encodePassword = $encoder->encodePassword($password, $actor->getSalt());
                $actor->setPassword($encodePassword);
            }else{
                $actor->setPassword($oldPassword);
            }
            $em->persist($actor);
            $em->flush();
            
            //image
            $filesData = $request->files->get('base_actor_edit');
            if (isset($filesData['image']['file']) && $filesData['image']['file'] instanceof UploadedFile) {            
                $this->get('core_manager')->uploadProfileImage($actor);
            }

            $this->get('session')->getFlashBag()->add('success', 'actor.edited');
            
            return $this->redirectToRoute('core_baseactor_show', array('id' => $actor->getId()));
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
    public function deleteAction(Request $request, BaseActor $actor)
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
    private function createDeleteForm(BaseActor $actor)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('core_baseactor_delete', array('id' => $actor->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    /**
     * Creates a new Newsletter entity.
     *
     * @param Request $request The request
     *
     * @return array|RedirectResponse
     *
     * @Route("/admin/baseactor/{id}/email")
     * @Template("CoreBundle:BaseActor:email.html.twig")
     */
    public function emailAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Newsletter $entity */
        $entity = $em->getRepository('CoreBundle:BaseActor')->find($id);
        
        $form = $this->createForm(ActorEmailType::class, null, array('email' => $entity->getEmail()));
       
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
                    return $this->redirect($this->generateUrl('core_baseactor_show', array('id' => $entity->getId())));
                }

            }
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    
    /**
     *
     * REGISTRATION
     *
     * Create new Actor entity.
     *
     * @Route("/register", name="register")
     * @Method({"GET", "POST"})
     * @Template("CoreBundle:Registration:index.html.twig")
     */
    public function registerAction(Request $request)
    {
        if($request->request->get('registration_short')){
            $formTemplate =  '_form.short.html.twig';
            $registration = new RegistrationShort();
            $form = $this->createForm('CoreBundle\Form\RegistrationShortType', $registration, array('translator' => $this->get('translator')));
        }else{
            $formTemplate = '_form.html.twig';
            $registration = new Registration();
            $form = $this->createForm('CoreBundle\Form\RegistrationType', $registration, array('translator' => $this->get('translator')));
        }
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
   
            $referer = $this->get('core_manager')->getRefererPath($request);
            if ($referer == '/identification') {
                return $this->redirect($this->generateUrl('ecommerce_checkout_detail'));
            }
            
            if ($request->isXmlHttpRequest()) {
                $result = array('success' => true);
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            return $this->redirect($this->generateUrl('core_baseactor_profile'));

        }
       
        return array(
            'form_template' => $formTemplate,
            'form' => $form->createView()
            );
    }

   /**
     * Validate registration.
     *
     * @Route("/validate/{email}/{hash}", name="register_validate")
     * @Method("GET")
     * @Template("CoreBundle:Registration:validate.html.twig")
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
                 $user->setActive(true);
                 $em->persist($user);
                 $em->flush();

                 $this->get('core.mailer')->sendValidateEmailMessage($user);

             }
        }

    }
  
    /**
     * 
     * RECOVERY PASSWORD
     * 
     */

    /**
     * Validate registration.
     *
     * @Route("/recovery")
     * @Template("CoreBundle:RecoveryPassword:index.html.twig")
     */
    public function recoveryAction(Request $request)
    {
        $form = $this->createForm('CoreBundle\Form\RecoveryEmailType');
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getNormData();
            if(isset($data['email'])){
                $em = $this->getDoctrine()->getManager();
                $user = $em->getRepository('CoreBundle:BaseActor')->findOneByEmail($data['email']);
                
                if ($request->isXmlHttpRequest()) {
                    $returnValues = new stdClass();
                    if ($user instanceof BaseActor) {
                        $this->get('core.mailer')->sendRecoveryPasswordMessage($user);
                        $returnValues->status = 'success';
                        $returnValues->message = $this->get('translator')->trans('recovery.email.success');
                    } else {
                        $returnValues->status = 'error';
                        $returnValues->message = $this->get('translator')->trans('recovery.email.error');
                    }
                    $response = new Response();
                    $response->setContent(json_encode(array(
                        'answer' => $returnValues
                    )));
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }else{
                    if ($user instanceof BaseActor) {
                        $this->get('core.mailer')->sendRecoveryPasswordMessage($user); 
                        $this->get('session')->getFlashBag()->add('success', 'recovery.email.success');
                    } else {
                        $this->get('session')->getFlashBag()->add('danger', 'recovery.email.error');
                    }
                }
            }
            
        }
        
        return array('form' => $form->createView());
    }
    
    /**
     * Validate registration.
     *
     * @Route("/recovery/{email}", defaults={"email" = ""})
     * @Method("POST")
     * @Template("CoreBundle:RecoveryPassword:recovery.password.html.twig")
     */
    public function recoveryPasswordAction($email)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('CoreBundle:BaseActor')->findOneByEmail($email);
        $returnValues = new stdClass();
        if ($user instanceof BaseActor) {
            $this->get('core.mailer')->sendRecoveryPasswordMessage($user);
            $returnValues->status = 'success';
            $returnValues->message = $this->get('translator')->trans('recovery.email.success');
        } else {
            $returnValues->status = 'error';
            $returnValues->message = $this->get('translator')->trans('recovery.email.error');;
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
     * @Route("/recovery/{email}/{hash}")
     * @Method({"GET", "POST"})
     * @Template("CoreBundle:RecoveryPassword:new.html.twig")
     */
    public function recoveryPasswordFormAction(Request $request, $email, $hash)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('CoreBundle:BaseActor')->findOneByEmail($email);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find user.');
        }

        if ($user instanceof BaseActor && $user->getSalt() == $hash) {
            $options = array('hash'=>$hash);
            $form = $this->createForm('CoreBundle\Form\RecoveryPasswordType', null, $options);
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                //Encode pass
                $factory = $this->get('security.encoder_factory');
                $recovery = $form->getData();
                $newPassword = $recovery['password'];
                $hash = $recovery['hash'];
                $user = $em->getRepository('CoreBundle:BaseActor')->findOneBySalt($hash);
                $encoder = $factory->getEncoder(new BaseActor());

                if (!$user) {
                    throw $this->createNotFoundException('Unable to find user.');
                }
                $newSalt = md5(uniqid(null, true));
                $password = $encoder->encodePassword($newPassword, $newSalt);
                $user->setSalt($newSalt);
                $user->setPassword($password);
                $em->flush();

                $this->get('core.mailer')->sendRecoveryPasswordConfirmation($user);

                $this->get('session')->getFlashBag()->add('success', 'recovery.change.success');

            }
            
            return array('form' => $form->createView());
            
        } else {
            throw $this->createNotFoundException('Invalid parameters. {'.$hash.'}');
        }

    }
    
    /*
     * 
     * PROFILE
     * 
     */
    /**
     * Profile details
     *
     * @Route("/profile")
     * @Method({"GET","POST"})
     * @Template("CoreBundle:Profile:index.html.twig")
     * 
     */
    public function profileAction(Request $request)
    {
        $em = $this->container->get('doctrine')->getManager();
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        
        if($user->isGranted('ROLE_ADMIN')) {
            return $this->redirect( $this->generateUrl('admin_default_dashboard'));
        }

        $form = $this->createForm('CoreBundle\Form\ProfileUserType', $user);
        $form_pass = $this->createForm('CoreBundle\Form\PasswordType', $user);

         if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($user);
                $em->flush();
                $this->container->get('session')->getFlashBag()->add('success', 'profile.saved');
            }
        }
        
        return array(
            'form' => $form->createView(),
            'form_pass' => $form_pass->createView(),
        );
    }
    
 
    /**
     * Profile details
     *
     * @Route("/profile/{id}")
     * @Method("GET")
     * @Template("CoreBundle:Profile:show.html.twig")
     * 
     */
    public function profileShowAction(Actor $actor)
    {
        //stats
        //$this->get('core_manager')->setStats($actor);
                
        if (!is_object($actor)) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return array(
            'user' => $actor
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

        if ($id != $this->get('security.token_storage')->getToken()->getUser()->getId()) {
            throw $this->createNotFoundException('Can not upload anything to a profile that is not their own.');
        }
        
        $em = $this->container->get('doctrine')->getManager();
        $entity = $this->get('security.token_storage')->getToken()->getUser();
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
     * Profile details
     *
     * @Route("/profile/change-password")
     * @Method("POST")
     */
    public function changePasswordAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form_pass = $this->createForm('CoreBundle\Form\PasswordType', $user);

        if ($request->isMethod('POST')) {
            $form_pass->handleRequest($request);

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
                    $this->container->get('session')->getFlashBag()->add('success', 'profile.password.saved');
                }else{
                    $this->container->get('session')->getFlashBag()->add('warning', 'profile.password.error');
                }
            }else{
                 $this->container->get('session')->getFlashBag()->add('warning', 'profile.password.error');
            }
        }
        
        $url = $this->container->get('router')->generate('core_baseactor_profile');
        return new RedirectResponse($url);

    }
    
}
