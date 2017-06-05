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
use CoreExtraBundle\Form\EmailType as ActorEmailType;
use CoreExtraBundle\Entity\NewsletterShipping;
use CoreExtraBundle\Entity\Newsletter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use CoreBundle\Entity\Image;
use EcommerceBundle\Entity\Address;
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
        $jsonList->setRepository($em->getRepository($this->get('core_manager')->getActorBundleName().':Actor'));
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
        $actorClass = $this->container->get('core_manager')->getActorClass();
        $actor = new $actorClass();
        $form = $this->createForm($this->get('core_manager')->getActorBundleName().'\Form\ActorType', $actor);
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
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $actor = $em->getRepository($this->get('core_manager')->getActorBundleName().':Actor')->findOneById($id);
        
        $deleteForm = $this->createDeleteForm($actor);
        
        $returnValues = array(
            'entity' => $actor,
            'delete_form' => $deleteForm->createView(),
        );
                
        $twigGlobal = $this->get('twig.global');
        if($twigGlobal->checkUse('CoreExtraBundle')){
            $shippingForm = $this->createForm('CoreExtraBundle\Form\EmailType', null, array('email' => $actor->getEmail()));
            $returnValues['shippingForm'] = $shippingForm->createView();
        }
        if($this->get('core_manager')->useEcommerce()){
            $addressForm = $this->createForm('EcommerceBundle\Form\AddressType', null, array('token_storage' => $this->container->get('security.token_storage')));
            $returnValues['addressForm'] = $addressForm->createView();
        }
        
        return array_merge($returnValues, array(
            'entity' => $actor,
            'delete_form' => $deleteForm->createView(),
        ));
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
    private function createDeleteForm($actor)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('core_actor_delete', array('id' => $actor->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
}
