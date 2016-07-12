<?php

namespace CoreBundle\Controller;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CoreBundle\Entity\Newsletter;
use CoreBundle\Form\NewsletterType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use CoreBundle\Entity\NewsletterShipping;
use CoreBundle\Form\NewsletterShippingType;
use CoreBundle\Entity\EmailToken;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;


/**
 * Newsletter controller.
 *
 * @Route("/admin")
 */
class NewsletterController extends Controller
{
    
    /******************************/
    /*****************************/
    /********SUBSCRIPTION*********/
    
    /**
     * Lists all Subscriptors entities.
     *
     * @return array
     *
     * @Route("/subscription")
     * @Method("GET")
     * @Template("CoreBundle:Newsletter:subscription.html.twig")
     */
    public function subscriptionAction()
    {
        return array();
    }
    
    /**
     * Returns a list of Subscriptors entities in JSON format.
     *
     * @return JsonResponse
     *
     * @Route("/subscription/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listSubscriptionJsonAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('CoreBundle:NewsletterSubscription'));
        $response = $jsonList->get();

        return new JsonResponse($response);
    }
    
    /**
     * Edits an existing Subscriptors entity.
     *
     * @param Request $request The request
     * @param int     $id      The entity id
     *
     * @throws NotFoundHttpException
     * @return array|RedirectResponse
     *
     * @Route("/subscription/{id}/disable")
     */
    public function disableAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Actor $entity */
        $entity = $em->getRepository('CoreBundle:Actor')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Actor  entity.');
        }
        
        
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (!$user->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('core_newsletter_index'));
        }
        
        $entity->setNewsletter(false);
        
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'newsletter.subscripts.disable');

        return $this->redirect($this->generateUrl('core_newsletter_subscription'));
        
    }
    
    
    /**
     * Lists all Newsletter entities.
     *
     * @return array
     *
     * @Route("/newsletter/")
     * @Method("GET")
     * @Template("CoreBundle:Newsletter:newsletter.html.twig")
     */
    public function indexAction()
    {
        return array();
    }

    

    /**
     * Returns a list of Newsletter entities in JSON format.
     *
     * @return JsonResponse
     *
     * @Route("/newsletter/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listJsonAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('CoreBundle:Newsletter'));
        $response = $jsonList->get();

        return new JsonResponse($response);
    }
    
    /**
     * Creates a new Newsletter entity.
     *
     * @param Request $request The request
     *
     * @return array|RedirectResponse
     *
     * @Route("/newsletter/")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $entity  = new Newsletter();
        $form = $this->createForm(new NewsletterType(), $entity);
        $form->handleRequest($request);

         
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->setVisible(true);
            $em->persist($entity);
            $em->flush();

           
            //if come from popup
            if ($request->isXMLHttpRequest()) {         
                return new JsonResponse(array(
                            'id' => $entity->getId(), 
                            'title' => $entity->getTitle()
                        ));
            }

            $this->get('session')->getFlashBag()->add('success', 'newsletter.created');
            
            return $this->redirect($this->generateUrl('core_newsletter_show', array('id' => $entity->getId())));
        }

        return $this->render( 'CoreBundle:Newsletter:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new Newsletter entity.
     *
     * @return array
     *
     * @Route("/newsletter/new")
     * @Method("GET")
     * @Template("CoreBundle:Newsletter:new.html.twig")
     */
    public function newAction()
    {
        $entity = new Newsletter();
        $form   = $this->createForm(new NewsletterType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Newsletter entity.
     *
     * @param int $id The entity id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/newsletter/{id}")
     * @Method("GET")
     * @Template("CoreBundle:Newsletter:show.html.twig")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Newsletter $entity */
        $entity = $em->getRepository('CoreBundle:Newsletter')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Newsletter entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Newsletter entity.
     *
     * @param int $id The entity id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/newsletter/{id}/edit")
     * @Method("GET")
     * @Template("CoreBundle:Newsletter:edit.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Newsletter $entity */
        $entity = $em->getRepository('CoreBundle:Newsletter')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Newsletter entity.');
        }

        $editForm = $this->createForm(new NewsletterType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Newsletter entity.
     *
     * @param Request $request The request
     * @param int     $id      The entity id
     *
     * @throws NotFoundHttpException
     * @return array|RedirectResponse
     *
     * @Route("/newsletter/{id}")
     * @Method("PUT")
     * @Template("CoreBundle:Newsletter:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Newsletter $entity */
        $entity = $em->getRepository('CoreBundle:Newsletter')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Newsletter entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new NewsletterType(), $entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'newsletter.edited');

            return $this->redirect($this->generateUrl('core_newsletter_show', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Newsletter entity.
     *
     * @param Request $request The request
     * @param int     $id      The entity id
     *
     * @throws NotFoundHttpException
     * @return RedirectResponse
     *
     * @Route("/newsletter/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /** @var Newsletter $entity */
            $entity = $em->getRepository('CoreBundle:Newsletter')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Newsletter entity.');
            }
            $em->remove($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'newsletter.deleted');
        }

        return $this->redirect($this->generateUrl('core_newsletter_index'));
    }

    
    
    /**
     * Creates a form to delete a Newsletter entity by id.
     *
     * @param int $id The entity id
     *
     * @return Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', HiddenType::class)
            ->getForm();
    }
    
    
    /******************************/
    /*****************************/
    /*********SHIPPING*************/
    
     /**
     * Lists all NewsletterShipping entities.
     *
     * @return array
     *
     * @Route("/shipping/")
     * @Method("GET")
     * @Template("CoreBundle:Newsletter/Shipping:index.html.twig")
     */
    public function shippingAction()
    {
        return array();
    }

    /**
     * Returns a list of NewsletterShipping entities in JSON format.
     *
     * @return JsonResponse
     *
     * @Route("/shipping/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listShippingJsonAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('CoreBundle:NewsletterShipping'));
        $response = $jsonList->get();

        return new JsonResponse($response);
    }
    
    
   /**
     * Returns a list of Transaction entities for a given user in JSON format.
     *
     * @param int $userId The user id
     *
     * @return JsonResponse
     *
     * @Route("/shipping/{actorId}/list.email.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listforUserJsonAction($actorId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \Kitchenit\AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('CoreBundle:NewsletterShipping'));
        $jsonList->setEntityId($actorId);

        $response = $jsonList->get();

        return new JsonResponse($response);
    }
    
    /**
     * Creates a new Shipping entity.
     *
     * @param Request $request The request
     *
     * @return array|RedirectResponse
     *
     * @Route("/shipping/")
     * @Method("POST")
     * @Template("CoreBundle:Newsletter/Shipping:new.html.twig")
     */
    public function createShippingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity  = new NewsletterShipping();
        $data = $request->request->get('corebundle_newslettershippingtype');
        $formConfig = array();
        if(isset($data['type']) && $data['type'] == 'token'){
            $formConfig['token'] = true;
        }
        $form = $this->createForm(new NewsletterShippingType($formConfig), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $emailArray = $this->getSubscriptorFromType($entity);
            $entity->setTotalSent(count($emailArray));
            
            /////////////////////////////////////////////////////////
            //Send every email with diferent token and store on DB //
            /////////////////////////////////////////////////////////
            if($entity->getType() == NewsletterShipping::TYPE_TOKEN){
                 //truncate table email token
                $em = $this->getDoctrine()->getEntityManager();
                $connection = $em->getConnection();
                $statement = $connection->prepare("TRUNCATE TABLE email_token");
                $statement->execute();
                
                foreach ($emailArray as $email) {
                    $emailToken = new EmailToken();
                    $emailToken->setEmail($email);
                    $token = sha1(uniqid());
                    $emailToken->setToken($token);
                    $em->persist($emailToken);
                    $body = preg_replace('/(#TOKEN#)/', $token, $entity->getNewsletter()->getBody());
                    $this->get('core.mailer')->sendShipping(array($email), NewsletterShipping::TYPE_TOKEN, $body);
                }
            }else{
                $body = $entity->getNewsletter()->getBody();
                $this->get('core.mailer')->sendShipping($emailArray, $entity->getType(), $body);
            }
            $em->persist($entity);
            $em->flush();

            //if come from popup
            if ($request->isXMLHttpRequest()) {         
                return new JsonResponse(array(
                            'id' => $entity->getId()
                        ));
            }
            
            $this->get('session')->getFlashBag()->add('success', 'newsletter.shipping.created');

            return $this->redirect($this->generateUrl('core_newsletter_showshipping', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    private function getSubscriptorFromType(NewsletterShipping $entity)
    {
        $emailArray  = array();
        $em = $this->getDoctrine()->getManager();
        $query =  null;
        if($entity->getType() == NewsletterShipping::TYPE_SUBSCRIPTS){
            $query = ' SELECT a'
                . ' FROM CoreBundle:NewsletterSubscription a'
                ;
        }elseif($entity->getType() == NewsletterShipping::TYPE_USER){
            $query = ' SELECT a'
                . ' FROM CoreBundle:Actor a'
                . " WHERE a.newsletter =  true "
                ;
        }
        
        if(is_null($query)) throw $this->createNotFoundException('No type found');
       
        $q = $em->createQuery($query);
        $entities = $q->getResult();
        foreach ($entities as $value) {
            ///////////////////////////////////////////////////
            ///////////////// test case ///////////////////////
            ///////////////////////////////////////////////////
            $core = $this->getParameter('core');
            if(preg_match($core['xpath_email'], $value->getEmail())){
                 $emailArray[] = $value->getEmail();
            }
            
        }
         
        return $emailArray;
    }
    
    /**
     * Displays a form to create a new NewsletterShipping entity.
     *
     * @return array
     *
     * @Route("/shipping/new")
     * @Method("GET")
     * @Template("CoreBundle:Newsletter/Shipping:new.html.twig")
     */
    public function newShippingAction()
    {
        $entity = new NewsletterShipping();
        $form   = $this->createForm(new NewsletterShippingType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    
    /**
     * Finds and displays a Newsletter entity.
     *
     * @param int $id The entity id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/shipping/{id}")
     * @Method("GET")
     * @Template("CoreBundle:Newsletter/Shipping:show.html.twig")
     */
    public function showShippingAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Newsletter $entity */
        $entity = $em->getRepository('CoreBundle:NewsletterShipping')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find NewsletterShipping entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * Deletes a NewsletterShipping entity.
     *
     * @param Request $request The request
     * @param int     $id      The entity id
     *
     * @throws NotFoundHttpException
     * @return RedirectResponse
     *
     * @Route("/shipping/{id}/delete")
     */
    public function deleteShippingAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Newsletter $entity */
        $entity = $em->getRepository('CoreBundle:NewsletterShipping')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find NewsletterShipping entity.');
        }

        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'newsletter.shipping.deleted');
        
        if($request->query->get('redirect')!=''){
            return  $this->redirect($request->query->get('redirect'));
        }
        
        return $this->redirect($this->generateUrl('core_newsletter_shipping'));
    }
}
