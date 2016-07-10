<?php

namespace CoreBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;


/**
 * Class NewsletterSubscriptionRepository
 */
class NewsletterSubscriptionRepository extends EntityRepository
{
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countTotal()
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(ns)');

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
            ->select('ns.id, ns.name, ns.email, ns.role');

        // search
        if (!empty($search)) {
            $qb->where('ns.name LIKE :search')
               ->orWhere('ns.email LIKE :search')
               ->orWhere('ns.role LIKE :search')     
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('ns.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('ns.name', $sortDirection);
                break;
            case 2:
                $qb->orderBy('ns.email', $sortDirection);
                break;
            case 2:
                $qb->orderBy('ns.role', $sortDirection);
                break;
        }

        
        
        return $qb->getQuery();
    }


    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('CoreBundle:NewsletterSubscription')
            ->createQueryBuilder('ns');

        return $qb;
    }
}