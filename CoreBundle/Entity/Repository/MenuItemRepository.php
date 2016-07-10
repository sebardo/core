<?php

namespace CoreBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use CoreBundle\Entity\MenuItem;


/**
 * Class MenuItemRepository
 */
class MenuItemRepository extends EntityRepository
{
    /**
     * Count the total of rows
     *
     * @param int|null $menuItemId The menuItem ID
     *
     * @return int
     */
    public function countTotal($menuItemId = null)
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(m)');

        if (!is_null($menuItemId)) {
            $qb->where('m.parentMenuItem = :menuItem_id')
                ->setParameter('menuItem_id', $menuItemId);
        }

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
    public function findAllForDataTables($search, $sortColumn, $sortDirection, $entityId=null)
    {
        $qb = $this->getQueryBuilder();

       
            // select
            $qb->select('m.id, m.name, m.order, m.active');
       
        if(is_null($entityId)){
            // where
            $qb->where('m.parentMenuItem IS NULL ');
        }else{
            // where
            $qb->where('m.parentMenuItem = :menuItem_id')
                ->setParameter('menuItem_id', $entityId);
        }


        // search
        if (!empty($search)) {
            $qb->andWhere('m.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('m.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('m.name', $sortDirection);
                break;
            case 2:
                $qb->orderBy('m.order', $sortDirection);
                break;
            case 3:
                $qb->orderBy('m.active', $sortDirection);
                break;
        }

        if($sortColumn=='') $qb->orderBy('m.order', 'ASC');
        return $qb->getQuery();
    }
 

    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('CoreBundle:MenuItem')
            ->createQueryBuilder('m');

        return $qb;
    }
    
}