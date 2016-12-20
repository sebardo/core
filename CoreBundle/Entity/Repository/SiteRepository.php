<?php

namespace CoreBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use CoreBundle\Entity\Site;


/**
 * Class SiteRepository
 */
class SiteRepository extends EntityRepository
{
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countTotal()
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(s)');

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
    public function findAllForDataTables($search, $sortColumn, $sortDirection)
    {
        // select
        $qb = $this->getQueryBuilder()
            ->select('s.id, s.name')
                ;

        // search
        if (!empty($search)) {
            $qb->where('s.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('s.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('s.name', $sortDirection);
                break;
        }

        return $qb->getQuery();
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
    public function findAllForDataTablesBySite($search, $sortColumn, $sortDirection, $siteId)
    {
        // select
        $qb = $this->getQueryBuilder()
             ->select('s.id, s.name')
            ;        
        
        // search
        if (!empty($search)) {
            $qb->where('s.name LIKE :search')
                ->andWhere('s.id = :siteId')
                ->setParameter('search', '%'.$search.'%');
        }else{
            $qb->where('s.id = :siteId');
        }

        $qb->setParameter('siteId', $siteId);
        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('s.name', $sortDirection);
                break;
        }

//        $qb->groupBy('u.username');
        
        return $qb->getQuery();
    } 

    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('CoreBundle:Site')
            ->createQueryBuilder('s');

        return $qb;
    }
}