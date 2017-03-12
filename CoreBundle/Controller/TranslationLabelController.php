<?php

namespace CoreBundle\Controller;

use CoreBundle\Entity\TranslationLabel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Common\Collections\ArrayCollection;

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
        $entity = new Translationlabel();
        $form = $this->createForm('CoreBundle\Form\TranslationLabelType', $entity);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //Create entry in DB
            $this->updateTranslation($entity);
            
            $this->get('session')->getFlashBag()->add('success', 'translation-label.created');
            
            return $this->redirectToRoute('translation-label_show', array('key' => $key, 'domain' => $domain));
        }

        return $this->render('CoreBundle:TranslationLabel:new.html.twig', array(
            'translationLabel' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a translationLabel entity.
     *
     * @Route("/{key}/{domain}", name="translation-label_show")
     * @Method("GET")
     */
    public function showAction(Request $request, $key, $domain)
    {
//        $deleteForm = $this->createDeleteForm($translationLabel);

        $entity = $this->get('asm_translation_loader.translation_manager')
            ->findTranslationBy(
                array(
                    'transKey' => $key,
                    'transLocale' => $request->getLocale(),
                    'messageDomain' => $domain,
                )
            );
        
        return $this->render('translationlabel/show.html.twig', array(
            'entity' => $entity,
//            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing translationLabel entity.
     *
     * @Route("/{key}/{domain}/edit", name="translation-label_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $key, $domain)
    {
        $translation = $this->get('asm_translation_loader.translation_manager')
            ->findTranslationBy(
                array(
                    'transKey' => $key,
                    'transLocale' => $request->getLocale(),
                    'messageDomain' => $domain,
                )
            );
                
        $entity = new Translationlabel();
        $entity->setKey($translation->getTransKey());
        $entity->setDomain($domain);
        foreach ($this->getParameter('a2lix_translation_form.locales') as $k => $loc) {
            
            $translation = $this->get('asm_translation_loader.translation_manager')
                ->findTranslationBy(
                    array(
                        'transKey' => $key,
                        'transLocale' => $loc,
                        'messageDomain' => $domain,
                    )
                );
            $transLabel = new \CoreBundle\Entity\TranslationLabelTranslation();
            $transLabel->setLocale($loc);
            $transLabel->setValue($translation->getTranslation());
            $entity->addTranslation($transLabel);
        }
        
        //$deleteForm = $this->createDeleteForm($translation);
        $editForm = $this->createForm('CoreBundle\Form\TranslationLabelType', $entity);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            
            //Create entry in DB
            $this->updateTranslation($entity);
            
            $this->get('session')->getFlashBag()->add('success', 'translationLabel.edited');
            
            return $this->redirectToRoute('translation-label_edit', array('key' => $key, 'domain' => $domain));
        }

        return $this->render('CoreBundle:TranslationLabel:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
//            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a translationLabel entity.
     *
     * @Route("/{key}/{domain}", name="translation-label_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $key, $domain)
    {
//        $form = $this->createDeleteForm($translationLabel);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $em = $this->getDoctrine()->getManager();
//            $em->remove($translationLabel);
//            $em->flush($translationLabel);
//            
//            $this->get('session')->getFlashBag()->add('success', 'translationLabel.deleted');
//        }

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
    
    public function updateTranslation($entity) 
    {
        $translationManager = $this->get('asm_translation_loader.translation_manager');
            
        //check if exist key "es"
        $translation = $translationManager->findTranslationBy(
            array(
                'transKey'      => $entity->getKey(),
                'transLocale'   => 'es',
                'messageDomain' => 'messages',
            )
        );
        // insert if no entry exists
        if (!$translation) {
            $translation = $translationManager->createTranslation();
            $translation->setTransKey($entity->getKey());
            $translation->setTransLocale('es');
            $translation->setMessageDomain('messages');
        }

        // and in either case we want to add a message :-)
        $translation->setTranslation($entity->getValue());

        $translationManager->updateTranslation($translation);

        //other translation
        foreach ($entity->getTranslations() as $trans) {
            $translation = $translationManager->findTranslationBy(
                array(
                    'transKey'      => $entity->getKey(),
                    'transLocale'   => $trans->getLocale(),
                    'messageDomain' => 'messages',
                )
            );

            // insert if no entry exists
            if (!$translation) {
                $translation = $translationManager->createTranslation();
                $translation->setTransKey($entity->getKey());
                $translation->setTransLocale($trans->getLocale());
                $translation->setMessageDomain('messages');
            }

            // and in either case we want to add a message :-)
            $translation->setTranslation($trans->getValue());

            $translationManager->updateTranslation($translation);

        }
        $fs = new Filesystem();
        $fs->remove($this->container->getParameter('kernel.cache_dir').'/translations');

    }
}
