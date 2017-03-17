<?php
namespace CoreBundle\Entity\Repository;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class BaseActorRepository extends EntityRepository implements UserLoaderInterface
{
    public function loadUserByUsername($username)
    {

        $q = $this
            ->createQueryBuilder('u')
            ->select('u, r')
            ->leftJoin('u.roles', 'r')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery();

        try {
            // The Query::getSingleResult() method throws an exception
            // if there is no record matching the criteria.
            $user = $q->getSingleResult();
        } catch (NoResultException $e) {
            $message = sprintf(
                'Unable to find an active admin CoreBundle:Actor object identified by "%s".',
                $username
            );
            throw new UsernameNotFoundException($message, 0, $e);
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }

        return $this->find($user->getId());
    }

    public function supportsClass($class)
    {
        return $this->getEntityName() === $class
            || is_subclass_of($class, $this->getEntityName());
    }
    
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countTotal()
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(u)');

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
            ->select('u.id, u.email, u.name, u.lastname, i.path actorImage');

        // join
        $qb->leftJoin('u.roles', 'r');
        $qb->leftJoin('u.image', 'i');
                
        // search
        if (!empty($search)) {
            // where('u.email LIKE :search')
            $qb->where('u.email LIKE :search')
                ->orWhere('u.name LIKE :search')
                ->orWhere('u.lastname LIKE :search')
                ->andWhere('r.role = :role')
                ->setParameter('role', 'ROLE_USER')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('u.id', $sortDirection);
                break;
            case 2:
                $qb->orderBy('u.email', $sortDirection);
                break;
            case 3:
                $qb->orderBy('u.name', $sortDirection);
                break;
            case 4:
                $qb->orderBy('u.lastname', $sortDirection);
                break;
        }

        return $qb->getQuery();
    }
    
    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('CoreBundle:BaseActor')
            ->createQueryBuilder('u');

        return $qb;
    }
}
