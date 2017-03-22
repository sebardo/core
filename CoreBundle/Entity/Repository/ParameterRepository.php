<?php

namespace CoreBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ParameterRepository
 */
class ParameterRepository  extends EntityRepository
{

    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countTotal()
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(p)');

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
            ->select('p.id, p.parameter, p.value');

        // search
        if (!empty($search)) {
            $qb->where('p.parameter LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('p.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('p.parameter', $sortDirection);
                break;
            case 2:
                $qb->orderBy('p.value', $sortDirection);
                break;
        }

        return $qb->getQuery();
    }
    
    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('CoreBundle:Parameter')
            ->createQueryBuilder('p');
            
        return $qb;
    }

}
