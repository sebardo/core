<?php

namespace CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CoreBundle\Entity\MenuItem;
use CoreBundle\Entity\Image;

/**
 * SubMenuItem controller.
 *
 * @Route("/admin/menuitems/{menuItemId}/submenuitems")
 */
class SubMenuItemController extends Controller
{
    /**
     * Lists all submenuitems from a MenuItem entity.
     *
     * @param int $menuItemId The MenuItem id
     *
     * @Route("/")
     * @Method("GET")
     * @Template("CoreBundle:SubMenuItem:index.html.twig")
     */
    public function indexAction(MenuItem $menuItemId)
    {
        return array(
            'menuitem' => $menuItemId,
        );
    }

    /**
     * Returns a list of submenuitems from a MenuItem entity in JSON format.
     *
     * @param int $menuItemId The MenuItem id
     *
     * @return JsonResponse
     *
     * @Route("/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listJsonAction($menuItemId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \Kitchenit\AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('CoreBundle:MenuItem'));
        $jsonList->setEntityId($menuItemId);

        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Displays a form to create a new MenuItem entity.
     *
     * @Route("/new")
     * @Method({"GET", "POST"})
     * @Template("CoreBundle:SubMenuItem:new.html.twig")
     */
    public function newAction(Request $request, MenuItem $menuItemId)
    {
        $entity = new MenuItem();
        $form   = $this->createForm('CoreBundle\Form\SubMenuItemType', $entity);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->getParentMenuItem($menuItemId);
            $em->persist($entity);
            $em->flush();
            
            //images
            $image = $form->getNormData()->getImage();
            if ($image instanceof Image) {
                $this->get('core_manager')->uploadMenuImage($entity);
            }
            
            $this->get('session')->getFlashBag()->add('success', 'menu.created');

            return $this->redirectToRoute('core_menuitem_show', array('id' => $entity->getId()));
        }
        
        return array(
            'entity' => $entity,
            'menuitem' => $menuItemId,
            'form'   => $form->createView(),
        );
    }
}
