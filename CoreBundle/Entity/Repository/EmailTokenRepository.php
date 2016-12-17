<?php

namespace CoreBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;


/**
 * Class EmailTokenRepository
 */
class EmailTokenRepository extends EntityRepository
{
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countTotal()
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(e)');

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
            ->select('e.id, e.email, e.token, e.active, e.created');
     
        // search
        if (!empty($search)) {
            // where('u.email LIKE :search')
            $qb->where('e.email LIKE :search')
                ->orWhere('e.token LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('e.id', $sortDirection);
                break;
            case 2:
                $qb->orderBy('e.email', $sortDirection);
                break;
            case 3:
                $qb->orderBy('e.token', $sortDirection);
                break;
            case 4:
                $qb->orderBy('e.active', $sortDirection);
                break;
        }

        return $qb->getQuery();
    }

    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('CoreBundle:EmailToken')
            ->createQueryBuilder('e');

        return $qb;
    }
}