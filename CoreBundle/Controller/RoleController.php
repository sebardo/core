<?php

namespace CoreBundle\Controller;

use CoreBundle\Entity\Role;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use CoreBundle\Entity\Actor;

/**
 * Role controller.
 *
 * @Route("/admin/role")
 */
class RoleController extends Controller
{
    /**
     * Lists all role entities.
     *
     * @Route("/")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $roles = $em->getRepository('CoreBundle:Role')->findAll();

        return array(
            'roles' => $roles,
        );
    }

    /**
     * Lists all role entities.
     *
     * @Route("/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")     
     */
    public function listJsonAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('CoreBundle:Role'));
        $jsonList->setLocale($request->getLocale());
        $response = $jsonList->get();
        

        return new JsonResponse($response);
    }

    /**
     * Returns a list of Transaction entities for a given user in JSON format.
     *
     * @param int $actorId The user id
     *
     * @return JsonResponse
     *
     * @Route("/role/{actorId}/list.email.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listforUserJsonAction($actorId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \Kitchenit\AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('CoreBundle:Role'));
        $jsonList->setEntityId($actorId);

        $response = $jsonList->get();

        return new JsonResponse($response);
    }
    
    
    /**
     * Add role by ajax
     *
     * @Route("/{id}/role/add")
     * @Method("POST")
     */
    public function addAction(Request $request, Actor $actor) 
    {
        $trans = $this->get('translator');
        $form = $this->container->get('form.factory')->create('CoreBundle\Form\ActorRoleType');
        $form->handleRequest($request);
           
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $data = $request->request->get($form->getName());
            $role = $em->getRepository('CoreBundle:Role')->find($data['roles']);
            $actor->addRole($role);
            try {
                $em->persist($actor);
                $em->flush($actor);
                return new JsonResponse(array('status' => 'success', 'id'=> $actor->getId(), 'message'=> $trans->trans('role.created')));
            } catch (\Exception $exc) {
                return new JsonResponse(array('status' => 'error', 'message'=> $trans->trans('role.duplicate')));
            }
        }
        return new JsonResponse(array('status' => 'error', 'message'=> $trans->trans('role.form.notsubmit')));
    }
    
    /**
     * Creates a new role entity.
     *
     * @Route("/new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $role = new Role();
        $form = $this->createForm('CoreBundle\Form\RoleType', $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($role);
            $em->flush($role);

            //if come from popup
            if ($request->isXMLHttpRequest()) {         
                return new JsonResponse(array(
                            'id' => $role->getId(), 
                        ));
            }
            $this->get('session')->getFlashBag()->add('success', 'role.created');
            
            return $this->redirectToRoute('core_role_show', array('id' => $role->getId()));
        }

        return array(
            'role' => $role,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a role entity.
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Role $role)
    {
        $deleteForm = $this->createDeleteForm($role);

        return array(
            'role' => $role,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing role entity.
     *
     * @Route("/{id}/edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Role $role)
    {
        $deleteForm = $this->createDeleteForm($role);
        $editForm = $this->createForm('CoreBundle\Form\RoleType', $role);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            //if come from popup
            if ($request->isXMLHttpRequest()) {         
                return new JsonResponse(array(
                            'id' => $role->getId(), 
                        ));
            }
            $this->get('session')->getFlashBag()->add('success', 'role.edited');
            
            return $this->redirectToRoute('core_role_edit', array('id' => $role->getId()));
        }

        return array(
            'role' => $role,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a role entity.
     *
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Role $role)
    {
        $form = $this->createDeleteForm($role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($role);
            $em->flush($role);
            
            $this->get('session')->getFlashBag()->add('success', 'role.deleted');
        }

        return $this->redirectToRoute('role_index');
    }

    /**
     * Deletes a role entity.
     *
     * @Route("/{actor}/{role}/delete")
     */
    public function deleteRoleActorAction(Request $request, Role $role, Actor $actor)
    {
        $em = $this->getDoctrine()->getManager();
        $actor->getRolesCollection()->removeElement($role);
       
        $em->flush();
            
        $this->get('session')->getFlashBag()->add('success', 'role.deleted');

        if($request->query->get('redirect')!=''){
            return  $this->redirect($request->query->get('redirect'));
        }
        return $this->redirectToRoute('core_role_index');
    }
    
    /**
     * Creates a form to delete a role entity.
     *
     * @param Role $role The role entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Role $role)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('core_role_delete', array('id' => $role->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
