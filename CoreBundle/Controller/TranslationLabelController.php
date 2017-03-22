<?php

namespace CoreBundle\Controller;

use CoreBundle\Model\TranslationLabel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Common\Collections\ArrayCollection;
use CoreBundle\Model\TranslationLabelTranslation;

/**
 * Translationlabel controller.
 *
 * @Route("/admin/translation-label")
 */
class TranslationLabelController extends Controller
{
    /**
     * Lists all translationLabel entities.
     *
     * @Route("/", name="translation-label_index")
     * @Method("GET")
     * @Template("CoreBundle:TranslationLabel:index.html.twig")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Lists all translationLabel entities.
     *
     * @Route("/list.{_format}", name="translation-label_listjson", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")     
     */
    public function listJsonAction(Request $request)
    {
        $totalEntities = $this->countTotal();
                
        $offset = intval($request->get('iDisplayStart'));
        $limit = intval($request->get('iDisplayLength'));
        $sortColumn = intval($request->get('iSortCol_0'));
        $sortDirection = $request->get('sSortDir_0');
        $search = $request->get('sSearch');
        $echo = intval($request->get('sEcho'));
        
        $entities = $this->findAllForDataTables($search, $sortColumn, $sortDirection, null, $request->getLocale());
        
        $totalFilteredEntities = count($entities->getScalarResult());

        // paginate
        $entities->setFirstResult($offset)
            ->setMaxResults($limit);

        $data = $entities->getResult();


        $response = array(
            'iTotalRecords'         => $totalEntities,
            'iTotalDisplayRecords'  => $totalFilteredEntities,
            'sEcho'                 => $echo,
            'aaData'                => $data
        );
        

        return new JsonResponse($response);
    }

    /**
     * Count the total of rows
     *
     * @param int|null $menuItemId The menuItem ID
     *
     * @return int
     */
    public function countTotal()
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('AsmTranslationLoaderBundle:Translation')
            ->createQueryBuilder('tl')
            ->select('COUNT(tl)');

        return $qb->getQuery()->getSingleScalarResult();
    }
    
    /**
     * Find all rows filtered for DataTables
     *
     * @param string   $search        The search string
     * @param int      $sortColumn    The column to sort by
     * @param string   $sortDirection The direction to sort the column
     * @param int|null $menuItemId    The menuItem ID
     *
     * @return \Doctrine\ORM\Query
     */
    public function findAllForDataTables($search, $sortColumn, $sortDirection, $entityId=null, $locale)
    {
     
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('AsmTranslationLoaderBundle:Translation')
            ->createQueryBuilder('tl');
       
        // select
        $qb->select('tl.transKey, tl.transLocale, tl.messageDomain, tl.translation ')
//           ->join('tl.translations', 't')
            ;
       
        //where
        $qb->where('tl.transLocale = :locale')
           ->setParameter('locale', $locale);
        // search
        if (!empty($search)) {
            $qb->andWhere('tl.transKey LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('tl.transKey', $sortDirection);
                break;
            case 2:
                $qb->orderBy('t.transLocale', $sortDirection);
                break;
            case 3:
                $qb->orderBy('tl.messageDomain', $sortDirection);
                break;
            case 4:
                $qb->orderBy('tl.translation', $sortDirection);
                break;
        }

        return $qb->getQuery();
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
            
            return $this->redirectToRoute('translation-label_show', array('key' => $entity->getKey(), 'domain' => 'messages'));
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
     * @Template()
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
        
        return array(
            'entity' => $entity,
//            'delete_form' => $deleteForm->createView(),
        );
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
            $transLabel = new TranslationLabelTranslation();
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
            
            return $this->redirectToRoute('translation-label_edit', array('key' => $key, 'domain' => 'messages'));
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
                'transLocale'   => 'en',
                'messageDomain' => 'messages',
            )
        );
        // insert if no entry exists
        if (!$translation) {
            $translation = $translationManager->createTranslation();
            $translation->setTransKey($entity->getKey());
            $translation->setTransLocale('en');
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
