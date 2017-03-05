<?php

namespace CoreBundle\Controller;

use CoreBundle\Entity\TranslationLabel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Translationlabel controller.
 *
 * @Route("translation-label")
 */
class TranslationLabelController extends Controller
{
    /**
     * Lists all translationLabel entities.
     *
     * @Route("/", name="translation-label_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $translationLabels = $em->getRepository('CoreBundle:TranslationLabel')->findAll();

        return $this->render('CoreBundle:TranslationLabel:index.html.twig', array(
            'translationLabels' => $translationLabels,
        ));
    }

    /**
     * Lists all translationLabel entities.
     *
     * @Route("/list.{_format}", name="translation-label_listjson", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")     
     */
    public function listJsonAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('CoreBundle:TranslationLabel'));
        $jsonList->setLocale($request->getLocale());
        $response = $jsonList->get();
        

        return new JsonResponse($response);
    }

    /**
     * Creates a new translationLabel entity.
     *
     * @Route("/new", name="translation-label_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $translationLabel = new Translationlabel();
        $form = $this->createForm('CoreBundle\Form\TranslationLabelType', $translationLabel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($translationLabel);
            $em->flush($translationLabel);

            //if come from popup
            if ($request->isXMLHttpRequest()) {         
                return new JsonResponse(array(
                            'id' => $translationLabel->getId(), 
                        ));
            }
            $this->get('session')->getFlashBag()->add('success', 'translation-label.created');
            
            return $this->redirectToRoute('translation-label_show', array('id' => $translationLabel->getId()));
        }

        return $this->render('CoreBundle:TranslationLabel:new.html.twig', array(
            'translationLabel' => $translationLabel,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a translationLabel entity.
     *
     * @Route("/{id}", name="translation-label_show")
     * @Method("GET")
     */
    public function showAction(TranslationLabel $translationLabel)
    {
        $deleteForm = $this->createDeleteForm($translationLabel);

        return $this->render('translationlabel/show.html.twig', array(
            'translationLabel' => $translationLabel,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing translationLabel entity.
     *
     * @Route("/{id}/edit", name="translation-label_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, TranslationLabel $translationLabel)
    {
        $deleteForm = $this->createDeleteForm($translationLabel);
        $editForm = $this->createForm('CoreBundle\Form\TranslationLabelType', $translationLabel);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            //if come from popup
            if ($request->isXMLHttpRequest()) {         
                return new JsonResponse(array(
                            'id' => $translationLabel->getId(), 
                        ));
            }
            $this->get('session')->getFlashBag()->add('success', 'translationLabel.edited');
            
            return $this->redirectToRoute('translation-label_edit', array('id' => $translationLabel->getId()));
        }

        return $this->render('translationlabel/edit.html.twig', array(
            'translationLabel' => $translationLabel,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a translationLabel entity.
     *
     * @Route("/{id}", name="translation-label_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, TranslationLabel $translationLabel)
    {
        $form = $this->createDeleteForm($translationLabel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($translationLabel);
            $em->flush($translationLabel);
            
            $this->get('session')->getFlashBag()->add('success', 'translationLabel.deleted');
        }

        return $this->redirectToRoute('translation-label_index');
    }

    /**
     * Creates a form to delete a translationLabel entity.
     *
     * @param TranslationLabel $translationLabel The translationLabel entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(TranslationLabel $translationLabel)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('translation-label_delete', array('id' => $translationLabel->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
