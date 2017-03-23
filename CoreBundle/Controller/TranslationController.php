<?php

namespace CoreBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Filesystem\Filesystem;
use CoreBundle\Model\Translation;
use CoreBundle\Model\TranslationTranslation;

/**
 * Translation controller.
 *
 * @Route("/admin/translations")
 */
class TranslationController extends Controller
{
    /**
     * Lists all translation entities.
     *
     * @Route("/")
     * @Method("GET")
     * @Template("CoreBundle:Translation:index.html.twig")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Lists all translation entities.
     *
     * @Route("/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
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
     * Creates a new translation entity.
     *
     * @Route("/new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new Translation();
        $form = $this->createForm('CoreBundle\Form\TranslationType', $entity);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //Create entry in DB
            $this->updateTranslation($entity);
            
            $this->get('session')->getFlashBag()->add('success', 'translation.created');
            
            return $this->redirectToRoute('core_translation_show', array('key' => $entity->getKey(), 'domain' => 'messages'));
        }

        return $this->render('CoreBundle:Translation:new.html.twig', array(
            'translation' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a translation entity.
     *
     * @Route("/{key}/{domain}")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Request $request, $key, $domain)
    {
        $deleteForm = $this->createDeleteForm($key, $domain);

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
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing translation entity.
     *
     * @Route("/{key}/{domain}/edit")
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
                
        $entity = new Translation();
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
            $trans = new TranslationTranslation();
            $trans->setLocale($loc);
            $trans->setValue($translation->getTranslation());
            $entity->addTranslation($trans);
        }
        
        $deleteForm = $this->createDeleteForm($key, $domain);
        $editForm = $this->createForm('CoreBundle\Form\TranslationType', $entity);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            
            //Create entry in DB
            $this->updateTranslation($entity);
            
            $this->get('session')->getFlashBag()->add('success', 'translation.edited');
            
            return $this->redirectToRoute('core_translation_show', array('key' => $key, 'domain' => 'messages'));
        }

        return $this->render('CoreBundle:Translation:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

   
    /**
     * Deletes a translation entity.
     *
     * @Route("/{key}/{domain}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $key, $domain)
    {

        $form = $this->createDeleteForm($key, $domain);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $translation = $this->get('asm_translation_loader.translation_manager')
                ->findTranslationBy(
                    array(
                        'transKey' => $key,
                        'transLocale' => $request->getLocale(),
                        'messageDomain' => $domain,
                    )
                );
            $this->get('asm_translation_loader.translation_manager')->removeTranslation($translation);
            $this->get('session')->getFlashBag()->add('success', 'translation.deleted');
        }

        return $this->redirectToRoute('core_translation_index');
    }

    /**
     * Creates a form to delete a translation entity.
     *
     * @param Translation $translation The translation entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($key, $domain)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('core_translation_delete', array('key' => $key, 'domain' => $domain)))
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
