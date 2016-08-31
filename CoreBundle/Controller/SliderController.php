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
use CoreBundle\Entity\Slider;
use CoreBundle\Form\SliderType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use A2lix\I18nDoctrineBundle\Annotation\I18nDoctrine;

/**
 * Slider controller.
 *
 * @Route("/admin/sliders")
 */
class SliderController extends Controller
{
    /**
     * Lists all Slider entities.
     *
     * @return array
     *
     * @Route("/")
     * @Method("GET")
     * @Template("CoreBundle:Slider:index.html.twig")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Returns a list of Slider entities in JSON format.
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
        $jsonList->setRepository($em->getRepository('CoreBundle:Slider'));
        $response = $jsonList->get();
        return new JsonResponse($response);
    }

    /**
     * Creates a new Slider entity.
     *
     * @Route("/new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $slider = new Slider();
        $form = $this->createForm('CoreBundle\Form\SliderType', $slider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($slider);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'slider.created');

            return $this->redirectToRoute('core_slider_show', array('id' => $slider->getId()));
        }

        return array(
            'entity' => $slider,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Slider entity.
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Slider $slider)
    {
        $deleteForm = $this->createDeleteForm($slider);

        return array(
            'entity' => $slider,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Slider entity.
     *
     * @Route("/{id}/edit")
     * @Method({"GET", "POST"})
     * @Template()
     * @I18nDoctrine
     */
    public function editAction(Request $request, Slider $slider)
    {
        
        $deleteForm = $this->createDeleteForm($slider);
        $editForm = $this->createForm('CoreBundle\Form\SliderType', $slider);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            if($slider->getRemoveImage()){
                $slider->setImage(null);
            }
            
            $em->persist($slider);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'slider.edited');
            
            return $this->redirectToRoute('core_slider_show', array('id' => $slider->getId()));
        }

        return array(
            'entity' => $slider,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Slider entity.
     *
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Slider $slider)
    {
        $form = $this->createDeleteForm($slider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($slider);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('info', 'slider.deleted');
        }

        return $this->redirectToRoute('core_slider_index');
    }

    /**
     * Creates a form to delete a Slider entity.
     *
     * @param Slider $slider The Slider entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Slider $slider)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('core_slider_delete', array('id' => $slider->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
