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
        $em = $this->getDoctrine()->getManager();

        $parameters = $em->getRepository('CoreBundle:Parameter')->findAll();

        return array(
            'parameters' => $parameters,
        );
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
        $parameter = new Parameter();
        $form = $this->createForm('CoreBundle\Form\ParameterType', $parameter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($parameter);
            $em->flush($parameter);

            //if come from popup
            if ($request->isXMLHttpRequest()) {         
                return new JsonResponse(array(
                            'id' => $parameter->getId(), 
                        ));
            }
            $this->get('session')->getFlashBag()->add('success', 'parameter.created');
            
            return $this->redirectToRoute('core_parameter_show', array('id' => $parameter->getId()));
        }

        return array(
            'parameter' => $parameter,
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
    public function showAction(Parameter $parameter)
    {
        $deleteForm = $this->createDeleteForm($parameter);

        return array(
            'parameter' => $parameter,
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
    public function editAction(Request $request, Parameter $parameter)
    {
        $deleteForm = $this->createDeleteForm($parameter);
        $editForm = $this->createForm('CoreBundle\Form\ParameterType', $parameter);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            //if come from popup
            if ($request->isXMLHttpRequest()) {         
                return new JsonResponse(array(
                            'id' => $parameter->getId(), 
                        ));
            }
            $this->get('session')->getFlashBag()->add('success', 'parameter.edited');
            
            return $this->redirectToRoute('core_parameter_edit', array('id' => $parameter->getId()));
        }

        return array(
            'parameter' => $parameter,
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
    public function deleteAction(Request $request, Parameter $parameter)
    {
        $form = $this->createDeleteForm($parameter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($parameter);
            $em->flush($parameter);
            
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
    private function createDeleteForm(Parameter $parameter)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('core_parameter_delete', array('id' => $parameter->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
