<?php

namespace CoreBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;


/**
 * Class NewsletterRepository
 */
class NewsletterRepository extends EntityRepository
{
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countTotal()
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(m)');

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
            ->select('m.id, m.title, m.body, m.active, m.created');

        $qb->where('m.visible = 1');
        // search
        if (!empty($search)) {
            $qb->andWhere('m.title LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('m.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('m.title', $sortDirection);
                break;
            case 2:
                $qb->orderBy('m.created', $sortDirection);
                break;
        }

        return $qb->getQuery();
    }


    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('CoreBundle:Newsletter')
            ->createQueryBuilder('m');

        return $qb;
    }
}