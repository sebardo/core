<?php

namespace CoreBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CoreBundle\Entity\Newsletter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use CoreBundle\Entity\NewsletterShipping;
use CoreBundle\Entity\EmailToken;


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
     * @Route("/newsletter/new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new Newsletter();
        $form = $this->createForm('CoreBundle\Form\NewsletterType', $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
            
            $this->get('session')->getFlashBag()->add('success', 'menu.created');

            return $this->redirectToRoute('core_newsletter_show', array('id' => $entity->getId()));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
        
    }

    /**
     * Finds and displays a Newsletter entity.
     *
     * @Route("/newsletter/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Newsletter $newsletter)
    {
        $deleteForm = $this->createDeleteForm($newsletter);

        return array(
            'entity' => $newsletter,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Newsletter entity.
     *
     * @Route("/newsletter/{id}/edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Newsletter $newsletter)
    {
        
        $deleteForm = $this->createDeleteForm($newsletter);
        $editForm = $this->createForm('CoreBundle\Form\NewsletterType', $newsletter);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($newsletter);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'newsletter.edited');
            
            return $this->redirectToRoute('core_newsletter_show', array('id' => $newsletter->getId()));
        }

        return array(
            'entity' => $newsletter,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * Deletes a Newsletter entity.
     *
     * @Route("/newsletter/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Newsletter $newsletter)
    {
        $form = $this->createDeleteForm($newsletter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->remove($newsletter);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('info', 'newsletter.deleted');
        }

        return $this->redirectToRoute('core_newsletter_index');
    }

   /**
     * Creates a form to delete a MenuItem entity.
     *
     * @param Newsletter $newsletter The Newsletter entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Newsletter $newsletter)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('core_newsletter_delete', array('id' => $newsletter->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
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
     * @Route("/shipping/new")
     * @Method({"GET", "POST"})
     * @Template("CoreBundle:Newsletter/Shipping:new.html.twig")
     */
    public function newShippingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity  = new NewsletterShipping();
        $data = $request->request->get('corebundle_newslettershippingtype');
        $formConfig = array();
        if(isset($data['type']) && $data['type'] == 'token'){
            $formConfig['token'] = true;
        }
        $form = $this->createForm('CoreBundle\Form\NewsletterShippingType', $entity, $formConfig);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $emailArray = $this->get('core_manager')->getSubscriptorFromType($entity);
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

    /**
     * Finds and displays a NewsletterShipping entity.
     *
     * @Route("/shipping/{id}")
     * @Method("GET")
     * @Template("CoreBundle:Newsletter/Shipping:show.html.twig")
     */
    public function showShippingAction(NewsletterShipping $newsletterShipping)
    {
        $deleteForm = $this->createNShippingDeleteForm($newsletterShipping);

        return array(
            'entity' => $newsletterShipping,
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
    public function deleteShippingAction(Request $request, NewsletterShipping $newsletterShipping)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($newsletterShipping);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'newsletter.shipping.deleted');
        
        if($request->query->get('redirect')!=''){
            return  $this->redirect($request->query->get('redirect'));
        }
        
        return $this->redirect($this->generateUrl('core_newsletter_shipping'));
    }
    
    /**
     * Creates a form to delete a MenuItem entity.
     *
     * @param Newsletter $newsletter The Newsletter entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createNShippingDeleteForm(NewsletterShipping $nshipping)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('core_newsletter_deleteshipping', array('id' => $nshipping->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
