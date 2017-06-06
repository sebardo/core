<?php

namespace CoreBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class PageRepository
 */
class PageRepository  extends EntityRepository
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
            ->select('p.id, t.title, t.description, p.active');

        //join
        $qb->leftJoin('p.translations', 't');
        
        // search
        if (!empty($search)) {
            $qb->where('t.title LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('p.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('t.title', $sortDirection);
                break;
            case 2:
                $qb->orderBy('t.description', $sortDirection);
                break;
            case 3:
                $qb->orderBy('p.active', $sortDirection);
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
    public function findOneBySlug($slug)
    {
        // select
        $qb = $this->getQueryBuilder()
            ->select('p.id, t.title, t.description, t.metaTitle, t.metaDescription, t.metaTags, p.active');

        //join
        $qb->leftJoin('p.translations', 't');
        
        $qb->where('t.slug LIKE :slug')
           ->andWhere('p.active LIKE :active')
            ->setParameter('slug', '%'.$slug.'%')
            ->setParameter('active', true);

        return $qb->getQuery()->getSingleResult();
    }
    
    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('CoreBundle:Page')
            ->createQueryBuilder('p');
            
        return $qb;
    }

}
