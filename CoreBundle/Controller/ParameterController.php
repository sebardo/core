<?php

namespace CoreBundle\Controller;

use CoreBundle\Entity\Parameter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Parameter controller.
 *
 * @Route("admin/parameters")
 */
class ParameterController extends Controller
{
    /**
     * Lists all parameter entities.
     *
     * @Route("/")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $form = $this->createDeleteListForm();
        return array('delete_form' => $form->createView());
    }

    /**
     * Lists all parameter entities.
     *
     * @Route("/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")     
     */
    public function listJsonAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('CoreBundle:Parameter'));
        $jsonList->setLocale($request->getLocale());
        $response = $jsonList->get();
        

        return new JsonResponse($response);
    }

    /**
     * Creates a new parameter entity.
     *
     * @Route("/new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new Parameter();
        $form = $this->createForm('CoreBundle\Form\ParameterType', $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush($entity);

            //if come from popup
            if ($request->isXMLHttpRequest()) {         
                return new JsonResponse(array(
                            'id' => $entity->getId(), 
                        ));
            }
            $this->get('session')->getFlashBag()->add('success', 'parameter.created');
            
            return $this->redirectToRoute('core_parameter_index');
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a parameter entity.
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Parameter $entity)
    {
        $deleteForm = $this->createDeleteForm($entity);

        return array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing parameter entity.
     *
     * @Route("/{id}/edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Parameter $entity)
    {
        $deleteForm = $this->createDeleteForm($entity);
        $editForm = $this->createForm('CoreBundle\Form\ParameterType', $entity);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            //if come from popup
            if ($request->isXMLHttpRequest()) {         
                return new JsonResponse(array(
                            'id' => $entity->getId(), 
                        ));
            }
            $this->get('session')->getFlashBag()->add('success', 'parameter.edited');
            
            return $this->redirectToRoute('core_parameter_index');
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a parameter entity.
     *
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Parameter $entity)
    {
        $form = $this->createDeleteForm($entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($entity);
            $em->flush($entity);
            
            $this->get('session')->getFlashBag()->add('success', 'parameter.deleted');
        }

        return $this->redirectToRoute('core_parameter_index');
    }

    /**
     * Creates a form to delete a parameter entity.
     *
     * @param Parameter $parameter The parameter entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Parameter $entity)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('core_parameter_delete', array('id' => $entity->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    /**
     * Creates a form to delete a route entity.
     *
     * @param Route $route The route entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteListForm()
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('core_parameter_delete', array('id' => 0)))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
