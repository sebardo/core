<?php
namespace CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BaseController  extends Controller
{
    protected function paginator($query, $page, $params=array(), $limit=10) 
    {
        
        $manager = $this->getDoctrine()->getManager();
        $q = $manager->createQuery($query);
       
        if(count($params)>0){
             $q->setParameters($params);
        }
        
        $entities = $q->getResult();
       
        
        if(count($entities)>1){
            $total = count($entities);
            $offset = $page * $limit - $limit;
            $pages = $total / $limit;
            $paginator = array();
            $paginator['total'] = $total;
            $paginator['pages'] = array();
            for ($index = 1; $index < $pages; $index++) {
                $paginator['pages'][] = $index;
            }
            $q = $manager->createQuery($query);
            
           
            if(count($params)>0){
                 $q->setParameters($params);
            }
            $entities2 = $q->setMaxResults($limit)
                ->setFirstResult($offset)
                ->getResult()
            ;

            return array($entities2, $paginator );
       }
       
       $paginator = array();
       $paginator['total'] = 1;
       $paginator['pages'] = array();

       return array($entities, $paginator);
    }
    
     protected function paginatorDirectory($query, $query2, $page, $params=array(), $params2=array(), $limit=10) 
    {
        
        $manager = $this->getDoctrine()->getManager();
        
        //first query whir pack > 1
        $q = $manager->createQuery($query);
        if(count($params)>0){
             $q->setParameters($params);
        }
//        $q->setFirstResult(1);
        $entities = $q->getResult();
        
        //second query pack = 1
        $q2 = $manager->createQuery($query2);
        if(count($params2)>0){
             $q2->setParameters($params2);
        }
        $entities2 = $q2->getResult();
       
       
        $entities = array_merge($entities, $entities2);        
        
        if(count($entities)>1){
            $total = count($entities);
            $offset = $page * $limit - $limit;
            $pages = $total / $limit;
            $paginator = array();
            $paginator['total'] = $total;
            $paginator['pages'] = array();
            for ($index = 1; $index < $pages; $index++) {
                $paginator['pages'][] = $index;
            }
            
            
            $returnValues = array();
            $x = 0;
            foreach ($entities as $entity) {
                if($x>=$offset && $x<($offset+$limit))
                $returnValues[] = $entity;
                $x++;
            }
       
            

            return array($returnValues, $paginator );
       }
       
       $paginator = array();
       $paginator['total'] = 1;
       $paginator['pages'] = array();

       return array($entities, $paginator);
    }
    
    protected function checkAccess($opticId) 
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if ($user->isGranted('ROLE_OPTIC') && $user->getId() != $opticId) {
            throw new AccessDeniedHttpException();
        }
    }
        
}
