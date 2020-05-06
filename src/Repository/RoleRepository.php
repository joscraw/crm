<?php

namespace App\Repository;

use App\Entity\Portal;
use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    // /**
    //  * @return Role[] Returns an array of Role objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Role
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findAllQueryBuilder()
    {
        $queryBuilder = $this->createQueryBuilder('role');

        return $queryBuilder;
    }

    /**
     * @param Portal $portal
     * @return mixed
     */
    public function getRolesByPortal(Portal $portal)
    {
        return $this->createQueryBuilder('role')
            ->where('role.portal = :portal')
            ->setParameter('portal', $portal->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Portal $portal
     * @return mixed
     */
    public function getRolesForUserFilterByPortal(Portal $portal)
    {
        return $this->createQueryBuilder('role')
            ->select('role.name as label')
            ->where('role.portal = :portal')
            ->setParameter('portal', $portal->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Portal $portal
     * @param $name
     * @return mixed
     */
    public function getRolesByNameAndPortal(Portal $portal, $name)
    {
        return $this->createQueryBuilder('role')
            ->where('role.portal = :portal')
            ->andWhere('role.name = :name')
            ->setParameter('portal', $portal->getId())
            ->setParameter('name', $name)
            ->getQuery()
            ->getResult();
    }

}
