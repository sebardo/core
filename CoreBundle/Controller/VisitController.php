<?php

namespace CoreBundle\Controller;

use CoreBundle\Entity\Visit;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use CoreBundle\Entity\EmailToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Visit controller.
 */
class VisitController extends Controller
{
    /**
     * Lists all visit entities.
     *
     * @Route("/visit/")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $visits = $em->getRepository('CoreBundle:Visit')->findAll();

        return array(
            'visits' => $visits,
        );
    }

    /**
     * Lists all visit entities.
     *
     * @Route("/visit/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")     
     */
    public function listJsonAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('CoreBundle:Visit'));
        $jsonList->setLocale($this->get('request_stack')->getCurrentRequest()->getLocale());
        $response = $jsonList->get();
        
        return new JsonResponse($response);
    }
    
     /**
     * Returns a list of Tokens entities in JSON format.
     *
     * @return JsonResponse
     *
     * @Route("/visit/feedbacklist.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function feedbackListJsonAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('CoreBundle:EmailToken'));
        $response = $jsonList->get();

        return new JsonResponse($response);
    }

     /**
     * @Route("/feedback")
     * @Template("CoreBundle:Visit:feedback.html.twig")
     */
    public function feedbackAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if($request->query->get('token') != ''){
            $token = $em->getRepository('CoreBundle:EmailToken')->findOneByToken($request->query->get('token'));
            if (!$token) {
                return $this->redirect($this->generateUrl('index'));
            }
            $token->setActive(true);
            $visit =  $em->getRepository('CoreBundle:Visit')->findOneByEmailToken($token);
            if (!$visit) {
                throw $this->createNotFoundException('Unable to find Visit entity.');
            }
            $visit->setFeedback(true);
            $em->flush();
            
            //Automatic login
            $token = new UsernamePasswordToken(
               $visit->getActor(),
               $visit->getActor()->getPassword(),
               'secured_area',
               $visit->getActor()->getRoles()
               );
            $this->get('security.token_storage')->setToken($token);
            if($request->getMethod() == 'POST'){
               die('post');
            }
            return array();
        }
        
        $this->redirect($this->generateUrl('index'));
        
    }
    
    /**
     * Creates a new visit entity.
     *
     * @Route("/visit/new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $visit = new Visit();
        $form = $this->createForm('CoreBundle\Form\VisitType', $visit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($visit);
            $em->flush($visit);

            //if come from popup
            if ($request->isXMLHttpRequest()) {         
                return new JsonResponse(array(
                            'id' => $visit->getId(), 
                        ));
            }
            
            /*********************/
            /********Custom ******/
            /*********************/
            //truncate table email token
//            $connection = $em->getConnection();
//            $statement = $connection->prepare("TRUNCATE TABLE email_token");
//            $statement->execute();
            $email = $visit->getActor()->getEmail();
            $emailToken = new EmailToken();
            $emailToken->setEmail($email);
            $token = sha1(uniqid());
            $emailToken->setToken($token);
            $em->persist($emailToken);
            $visit->setEmailToken($emailToken);
            $em->flush();
            $template   = $this->get('twig')->loadTemplate('CoreBundle:Email:email.template.html.twig');
            $body  = $template->renderBlock('body', array(
                'user_name' => $visit->getActor()->getFullName(),
                'place_name' => 'Place name'
            ));
            $body = preg_replace('/(#TOKEN#)/', $token, $body);  
            
            $this->get('core.mailer')->sendFeedback(array($email), $body, 'Place Name');
            $this->get('session')->getFlashBag()->add('success', 'visit.sent');

            $this->get('session')->getFlashBag()->add('success', 'visit.created');
            
            return $this->redirectToRoute('visit_show', array('id' => $visit->getId()));
        }

        return array(
            'visit' => $visit,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a visit entity.
     *
     * @Route("/visit/{id}")
     * @Method("GET")
     */
    public function showAction(Visit $visit)
    {
        $deleteForm = $this->createDeleteForm($visit);

        return array(
            'visit' => $visit,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing visit entity.
     *
     * @Route("/visit/{id}/edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Visit $visit)
    {
        $deleteForm = $this->createDeleteForm($visit);
        $editForm = $this->createForm('CoreBundle\Form\VisitType', $visit);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            //if come from popup
            if ($request->isXMLHttpRequest()) {         
                return new JsonResponse(array(
                            'id' => $visit->getId(), 
                        ));
            }
            $this->get('session')->getFlashBag()->add('success', 'visit.edited');
            
            return $this->redirectToRoute('visit_edit', array('id' => $visit->getId()));
        }

        return  array(
            'visit' => $visit,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a visit entity.
     *
     * @Route("/visit/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Visit $visit)
    {
        $form = $this->createDeleteForm($visit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($visit);
            $em->flush($visit);
            
            $this->get('session')->getFlashBag()->add('success', 'visit.deleted');
        }

        return $this->redirectToRoute('visit_index');
    }

    /**
     * Creates a form to delete a visit entity.
     *
     * @param Visit $visit The visit entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Visit $visit)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('visit_delete', array('id' => $visit->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
