<?php

namespace CoreBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use CoreBundle\Entity\Notification;
use CoreBundle\Entity\Optic;
use CoreBundle\Entity\Actor;


/**
 * Class NotificationRepository
 */
class NotificationRepository extends EntityRepository
{
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countTotal()
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(o)');

        return $qb->getQuery()->getSingleScalarResult();
    }
   
    
        
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function findOneByLikeDetail($actor, $actorDest, $type, $detail)
    {
        $qb = $this->getQueryBuilder();
        $notification = null;
        if($actorDest instanceof Actor) {
            $notification = $qb->where('n.actor = :actor')
                ->andWhere('n.actorDest = :actorDest')
                ->andWhere('n.type = :type')
                ->andWhere('n.detail LIKE :detail')
                ->setParameters(array(
                    'actor' => $actor, 
                    'actorDest' => $actorDest, 
                    'type' => $type,
                    'detail' => '%'.$detail.'%'
                    ))
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        }elseif($actorDest instanceof Optic) {
            
            $notification = $qb->where('n.actor = :actor')
                ->andWhere('n.opticDest = :opticDest')
                ->andWhere('n.type = :type')
                ->andWhere('n.detail LIKE :detail')
                ->setParameters(array(
                    'actor' => $actor, 
                    'opticDest' => $actorDest, 
                    'type' => $type,
                    'detail' => '%'.$detail.'%'
                    ))
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
            
        }
        
            
        return $notification;
    } 
    
    /**
     * Find all rows filtered for DataTables
     *
     * @param string $search        The search string
     * @param int    $sortColumn    The column to sort by
     * @param string $sortDirection The direction to sort the column
     *
     * @return \Doctrine\ORM\Query
     */
    public function findAllForDataTables($search, $sortColumn, $sortDirection)
    {
        // select
        $qb = $this->getQueryBuilder()
            ->select('n.id, a.id actorId, a.name actorName, ad.id actorDestId, ad.name actorDestName, n.type');

        // join
        $qb->leftJoin('n.actor', 'a')
            ->leftJoin('n.actorDest', 'ad')
                ;
                
        // search
        if (!empty($search)) {
            // where('u.email LIKE :search')
            $qb->where('a.name LIKE :search')
                ->orWhere('ad.name LIKE :search')
                ->orWhere('n.type LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('n.id', $sortDirection);
                break;
            case 2:
                $qb->orderBy('a.name', $sortDirection);
                break;
            case 3:
                $qb->orderBy('ad.name', $sortDirection);
                break;
            case 4:
                $qb->orderBy('n.type', $sortDirection);
                break;
        }

        return $qb->getQuery();
    }

    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('CoreBundle:Notification')
            ->createQueryBuilder('n');

        return $qb;
    }
}