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
use A2lix\I18nDoctrineBundle\Annotation\I18nDoctrine;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class MenuItemController extends Controller
{
    
    /**
     * @Route("/menu/{slug}")
     * @Template("CoreBundle:MenuItem:menu.item.html.twig")
     */
    public function menuAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        
        try {
            $qb = $em->getRepository('CoreBundle:MenuItem')->createQueryBuilder('p')
                    ->join('p.translations', 't')
                    ->where('t.slug = :slug')
                    ->setParameter('slug', $slug)
                    ->setMaxResults(1);
            $entity = $qb->getQuery()->getSingleResult();
        } catch (\Exception $exc) {
            $entity =  $em->getRepository('CoreBundle:MenuItem')->getTranslateMenuItemBySlug($slug, $request->getLocale());
            return $this->redirectToRoute('core_menuitem_menu', array('slug' => $entity->getSlug()));
        }

        return array(
            'item' => $entity
            );
    }
    
    
    
    /**
     * Lists all MenuItem entities.
     *
     * @return array
     *
     * @Route("/admin/menuitems/")
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
     * @Route("/admin/menuitems/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listJsonAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \Kitchenit\AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('CoreBundle:MenuItem'));
        $jsonList->setLocale($this->get('request_stack')->getCurrentRequest()->getLocale());
        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Creates a new MenuItem entity.
     *
     * @Route("/admin/menuitems/new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $menu = new MenuItem();
        $form = $this->createForm('CoreBundle\Form\MenuItemType', $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($menu);
            $em->flush();
            
            //images
            $image = $form->getNormData()->getImage();
            if ($image instanceof Image) {
                $this->get('core_manager')->uploadMenuImage($menu);
            }
            
            $this->get('session')->getFlashBag()->add('success', 'menu.created');

            return $this->redirectToRoute('core_menuitem_show', array('id' => $menu->getId()));
        }

        return array(
            'entity' => $menu,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a MenuItem entity.
     *
     * @Route("/admin/menuitems/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction(MenuItem $menuItem)
    {
        $em = $this->getDoctrine()->getManager();
        $deleteForm = $this->createDeleteForm($menuItem);
//        $menuItem = $em->getRepository('CoreBundle:MenuItem')->getItemsWithTranslations($menuItem);
        
        return array(
            'entity' => $menuItem,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing MenuItem entity.
     *
     * @Route("/admin/menuitems/{id}/edit")
     * @Method({"GET", "POST"})
     * @Template()
     * @I18nDoctrine
     */
    public function editAction(Request $request, MenuItem $menuItem)
    {
        
        $deleteForm = $this->createDeleteForm($menuItem);
        $editForm = $this->createForm('CoreBundle\Form\MenuItemType', $menuItem);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
//            $data = $request->request->get('menu_item');
//            print_r($data);die();
//            
            if($menuItem->getRemoveImage()){
                $menuItem->setImage(null);
            }
            
            $em->persist($menuItem);
            $em->flush();

            //images
            $filesData = $request->files->get('menu_item');
            if (isset($filesData['image']['file']) && $filesData['image']['file'] instanceof UploadedFile && $editForm->getNormData()->getImage() instanceof Image) {
                $this->get('core_manager')->uploadMenuImage($menuItem);
            }
            
            $this->get('session')->getFlashBag()->add('success', 'menu.edited');
            
            return $this->redirectToRoute('core_menuitem_show', array('id' => $menuItem->getId()));
        }

        return array(
            'entity' => $menuItem,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * Deletes a MenuItem entity.
     *
     * @Route("/admin/menuitems/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, MenuItem $menuItem)
    {
        $form = $this->createDeleteForm($menuItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //remove image folder
            $removeFolder = $this->get('core_manager')->getAbsolutePathMenuItem($menuItem->getId());
            $this->get('core_manager')->recurseRemove($removeFolder);
            
            $em = $this->getDoctrine()->getManager();
            $em->remove($menuItem);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('info', 'menu.deleted');
        }

        return $this->redirectToRoute('core_menuitem_index');
    }

   /**
     * Creates a form to delete a MenuItem entity.
     *
     * @param MenuItem $menuItem The MenuItem entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(MenuItem $menuItem)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('core_menuitem_delete', array('id' => $menuItem->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
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
     * @Route("/admin/menuitems/sort")
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
    
}
