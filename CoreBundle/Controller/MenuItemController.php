<?php

namespace CoreBundle\Controller;

use Doctrine\ORM\Query;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CoreBundle\Entity\MenuItem;
use CoreBundle\Form\MenuItemType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use CoreBundle\Entity\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * MenuItem controller.
 *
 * @Route("/admin/menuitems")
 */
class MenuItemController extends Controller
{
    /**
     * Lists all MenuItem entities.
     *
     * @return array
     *
     * @Route("/")
     * @Method("GET")
     * @Template("CoreBundle:MenuItem:index.html.twig")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Returns a list of MenuItem entities in JSON format.
     *
     * @return JsonResponse
     *
     * @Route("/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listJsonAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \Kitchenit\AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('CoreBundle:MenuItem'));
        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Creates a new MenuItem entity.
     *
     * @param Request $request The request
     *
     * @return array|RedirectResponse
     *
     * @Route("/")
     * @Method("POST")
     * @Template("CoreBundle:MenuItem:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new MenuItem();
        $form = $this->createForm(new MenuItemType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->container->get('security.context')->getToken()->getUser();
            
            $image = $form->getNormData()->getImage();
            if($image instanceof Image){
                $entity->setImage(null);
            }
            $em->persist($entity);
            $em->flush();
            
            if ($image instanceof Image && $image->getFile() instanceof UploadedFile) {
                $imagePath = $this->get('core_manager')->uploadMenuImage($image->getFile(), $entity);
                $img = new Image();
                $img->setPath($imagePath);
                $em->persist($img);
                $entity->setImage($img);
                $em->flush();
            }
            
            $this->get('session')->getFlashBag()->add('success', 'menu.created');

            return $this->redirect($this->generateUrl('core_menuitem_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new MenuItem entity.
     *
     * @return array
     *
     * @Route("/new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new MenuItem();
        $form   = $this->createForm(new MenuItemType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Sorts a list of features.
     *
     * @param Request $request
     * @param int     $menuitemId
     *
     * @throws NotFoundHttpException
     * @return array|Response
     *
     * @Route("/sort")
     * @Method({"GET", "POST"})
     * @Template
     */
    public function sortAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if ($request->isXmlHttpRequest()) {
            $this->get('admin_manager')->sort('CoreBundle:MenuItem', $request->get('values'));

            return new Response(0, 200);
        }

        $categories = $em->getRepository('CoreBundle:MenuItem')->findBy(
            array('parentMenuItem' => NULL),
            array('order' => 'asc')
        );

        return array(
            'categories' => $categories
        );
    }
    
    /**
     * Finds and displays a MenuItem entity.
     *
     * @param int $id The entity id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var MenuItem $entity */
        $entity = $em->getRepository('CoreBundle:MenuItem')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MenuItem entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing MenuItem entity.
     *
     * @param int $id The entity id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/{id}/edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var MenuItem $entity */
        $entity = $em->getRepository('CoreBundle:MenuItem')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MenuItem entity.');
        }

        $editForm = $this->createForm(new MenuItemType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing MenuItem entity.
     *
     * @param Request $request The request
     * @param int     $id      The entity id
     *
     * @throws NotFoundHttpException
     * @return array|RedirectResponse
     *
     * @Route("/{id}")
     * @Method("PUT")
     * @Template("CoreBundle:MenuItem:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var MenuItem $entity */
        $entity = $em->getRepository('CoreBundle:MenuItem')->find($id);
        $oldImage = $entity->getImage();
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MenuItem entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new MenuItemType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            
            $data = $editForm->getNormData();
            $image = $data->getImage();
 
            if ($image instanceof Image && $image->getFile() instanceof UploadedFile) {
                $imagePath = $this->get('core_manager')->uploadMenuImage($image->getFile(), $entity);
                $img = new Image();
                $img->setPath($imagePath);
                $em->persist($img);
                $entity->setImage($img);
            }else{
                $entity->setImage($oldImage);
            }
           
//            if($data->getRemoveImage()){
//                $entity->setImage(null);
//            }
            
            $em->persist($entity);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'menu.edited');

            return $this->redirect($this->generateUrl('core_menuitem_show', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a MenuItem entity.
     *
     * @param Request $request The request
     * @param int     $id      The entity id
     *
     * @throws NotFoundHttpException
     * @return RedirectResponse
     *
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /** @var MenuItem $entity */
            $entity = $em->getRepository('CoreBundle:MenuItem')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find MenuItem entity.');
            }

            $em->remove($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', 'menu.deleted');
        }

        return $this->redirect($this->generateUrl('core_menuitem_index'));
    }

    /**
     * Creates a form to delete a MenuItem entity by id.
     *
     * @param int $id The entity id
     *
     * @return Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }
    
    
}