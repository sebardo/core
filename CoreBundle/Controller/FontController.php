<?php

namespace CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CoreBundle\Entity\Font;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Font controller.
 *
 * @Route("/admin/fonts")
 */
class FontController extends Controller
{
    /**
     * Lists all Font entities.
     *
     * @Route("/")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
    
    /**
     * Returns a list of Font entities in JSON format.
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
        $jsonList->setRepository($em->getRepository('CoreBundle:Font'));
        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Creates a new Font entity.
     *
     * @Route("/new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $font = new Font();
        $form = $this->createForm('CoreBundle\Form\FontType', $font);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($font);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'font.created');
            
            return $this->redirectToRoute('core_font_show', array('id' => $font->getId()));
        }

        return array(
            'font' => $font,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/less")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function lessAction(Request $request)
    {
        
        $form = $this->createForm('CoreBundle\Form\BootstrapVariablesType');
        $form->handleRequest($request);
        $path = realpath(__DIR__.'/../Resources/public/less/custom-fonts.less');
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getNormData();
            $fs = new Filesystem();
            try {
                $fs->dumpFile($path, $data['vars']);
            } catch (IOExceptionInterface $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
            
        }
        
        return array(
            'path' => $path,
            'form' => $form->createView(),
        );
    }
    
    /**
     * Finds and displays a Font entity.
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Font $font)
    {
        $deleteForm = $this->createDeleteForm($font);

        return array(
            'entity' => $font,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Font entity.
     *
     * @Route("/{id}/edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Font $font)
    {
        $deleteForm = $this->createDeleteForm($font);
        $editForm = $this->createForm('CoreBundle\Form\FontType', $font);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($font);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'font.edited');
            
            return $this->redirectToRoute('core_font_edit', array('id' => $font->getId()));
        }

        return array(
            'entity' => $font,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Font entity.
     *
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Font $font)
    {
        $form = $this->createDeleteForm($font);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($font);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('danger', 'font.deleted');
        }

        return $this->redirectToRoute('core_font_index');
    }

    /**
     * Creates a form to delete a Font entity.
     *
     * @param Font $font The Font entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Font $font)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('core_font_delete', array('id' => $font->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    
}
