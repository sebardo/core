<?php

namespace CoreBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use CoreBundle\Entity\Site;


/**
 * Class RoleRepository
 */
class RoleRepository extends EntityRepository
{
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countTotal()
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(r)');

        return $qb->getQuery()->getSingleScalarResult();
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
    public function findAllForDataTables($search, $sortColumn, $sortDirection, $actorId = null)
    {
        // select
        $qb = $this->getQueryBuilder()
            ->select('r.id, r.name, r.role')
            ->leftJoin('r.users', 'a')
            ;

         // where
        if (!is_null($actorId)) {
            $qb->where('a.id = :actor_id')
                ->setParameter('actor_id', $actorId);
        }
        // search
        if (!empty($search)) {
            $qb->andWhere('r.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('r.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('r.name', $sortDirection);
                break;
        }

        return $qb->getQuery();
    }
            
    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('CoreBundle:Role')
            ->createQueryBuilder('r');

        return $qb;
    }
}