<?php

namespace CoreBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;


/**
 * Class SliderRepository
 */
class SliderRepository extends EntityRepository
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
            ->select('s.id, t.title, s.openInNewWindow, s.url, s.active, s.order')
            ->join('s.translations', 't');

        // search
        if (!empty($search)) {
            $qb->where('t.title LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('s.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('t.title', $sortDirection);
                break;
            case 2:
                $qb->orderBy('s.openInNewWindow', $sortDirection);
                break;
            case 4:
                $qb->orderBy('s.active', $sortDirection);
                break;
            case 5:
                $qb->orderBy('s.order', $sortDirection);
                break;
        }

        return $qb->getQuery();
    }

    /**
     * Find all rows with images
     *
     * @return array
     */
    public function findAllWithImages()
    {
        $qb = $this->getQueryBuilder()
            ->select('s, partial i.{id, path}')
            ->innerJoin('s.image', 'i')
            ->where('s.active = TRUE')
            ->orderBy('s.order');

        return $qb->getQuery()
            ->getResult();
    }

    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('CoreBundle:Slider')
            ->createQueryBuilder('s');

        return $qb;
    }
}