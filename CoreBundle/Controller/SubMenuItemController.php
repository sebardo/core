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
use CoreBundle\Form\SubMenuItemType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/")
     * @Method("GET")
     * @Template("CoreBundle:SubMenuItem:index.html.twig")
     */
    public function indexAction($menuItemId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var MenuItem $entity */
        $entity = $em->getRepository('CoreBundle:MenuItem')->find($menuItemId);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MenuItem entity.');
        }

        return array(
            'menuitem' => $entity,
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
     * Creates a new MenuItem entity.
     *
     * @param Request $request    The request
     * @param int     $menuItemId The MenuItem id
     *
     * @throws NotFoundHttpException
     * @return array|RedirectResponse
     *
     * @Route("/")
     * @Method("POST")
     * @Template("CoreBundle:MenuItem:new.html.twig")
     */
    public function createAction(Request $request, $menuItemId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var MenuItem $menuItem */
        $menuItem = $em->getRepository('CoreBundle:MenuItem')->find($menuItemId);

        if (!$menuItem) {
            throw $this->createNotFoundException('Unable to find MenuItem entity.');
        }

        $entity  = new MenuItem();
        $form = $this->createForm(new SubMenuItemType(), $entity);
        $entity->setParentMenuItem($menuItem);

        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'menu.created');

            return $this->redirect($this->generateUrl('core_menuitem_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'menuitem' => $menuitemy,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new MenuItem entity.
     *
     * @param int $menuItemId The MenuItem id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/new")
     * @Method("GET")
     * @Template("CoreBundle:SubMenuItem:new.html.twig")
     */
    public function newAction($menuItemId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var MenuItem $menuItem */
        $menuItem = $em->getRepository('CoreBundle:MenuItem')->find($menuItemId);

        if (!$menuItem) {
            throw $this->createNotFoundException('Unable to find MenuItem entity.');
        }

        $entity = new MenuItem();
        $form   = $this->createForm(new SubMenuItemType(), $entity);

        return array(
            'entity' => $entity,
            'menuitem' => $menuItem,
            'form'   => $form->createView(),
        );
    }
}
