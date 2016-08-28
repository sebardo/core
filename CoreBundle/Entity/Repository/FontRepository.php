<?php

namespace CoreBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use CoreBundle\Entity\MenuItem;


/**
 * Class FontRepository
 */
class FontRepository extends EntityRepository
{
    /**
     * Count the total of rows
     *
     * @param int|null $menuItemId The menuItem ID
     *
     * @return int
     */
    public function countTotal()
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(f)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Find all rows filtered for DataTables
     *
     * @param string   $search        The search string
     * @param int      $sortColumn    The column to sort by
     * @param string   $sortDirection The direction to sort the column
     * @param int|null $menuItemId    The menuItem ID
     *
     * @return \Doctrine\ORM\Query
     */
    public function findAllForDataTables($search, $sortColumn, $sortDirection)
    {
        $qb = $this->getQueryBuilder();
       
        // select
        $qb->select('f.id, f.name, f.active ')
            ;
       

        // search
        if (!empty($search)) {
            $qb->andWhere('f.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('f.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('f.name', $sortDirection);
                break;
            case 2:
                $qb->orderBy('f.active', $sortDirection);
                break;
        }

        return $qb->getQuery();
    }
 

    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('CoreBundle:Font')
            ->createQueryBuilder('f');

        return $qb;
    }
    
}