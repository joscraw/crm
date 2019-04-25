<?php

namespace App\Repository;

use App\Entity\CustomObject;
use App\Entity\Portal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CustomObject|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomObject|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomObject[]    findAll()
 * @method CustomObject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomObjectRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CustomObject::class);
    }

    // /**
    //  * @return CustomObject[] Returns an array of CustomObject objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CustomObject
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getDataTableData($start, $length, $search, $orders, $columns)
    {
        // Main Query
        $mainQuerySelectColumns = ['dt.id', 'dt.label', 'dt.createdAt'];
        $searchQuery = null;
        $query = $this->createQueryBuilder('dt')
            ->select($mainQuerySelectColumns);

        // Search
        if(!empty($search['value'])) {
            $searchItem = $search['value'];
            $searchQuery = 'dt.label LIKE \'%'.$searchItem.'%\'';
        }

        if ($searchQuery) {
            $query->andWhere($searchQuery);
        }

        // Limit
        $query->setFirstResult($start)->setMaxResults($length);

        // Order

        /*$query->addOrderBy('dt.label', 'ASC');*/

        foreach ($orders as $key => $order) {
            // Orders does not contain the name of the column, but its number,
            // so add the name so we can handle it just like the $columns array
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }

        foreach ($orders as $key => $order) {
            // $order['name'] is the name of the order column as sent by the JS
            if ($order['name'] != '') {

                $orderColumn = "dt.{$order['name']}";

                $query->orderBy($orderColumn, $order['dir']);
            }
        }

        $results = $query->getQuery()->getResult();
        $arrayResults = $query->getQuery()->getArrayResult();


        return array(
            "results" 		=> $results,
            "arrayResults"  => $arrayResults
        );

    }

    /**
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findCount()
    {
        return $this->createQueryBuilder('dt')
            ->select('count(dt.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param $internalName
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByInternalName($internalName)
    {
        return $this->createQueryBuilder('customObject')
            ->where('customObject.internalName = :internalName')
            ->setParameter('internalName', $internalName)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $label
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByLabel($label)
    {
        return $this->createQueryBuilder('customObject')
            ->orWhere('customObject.label = :label')
            ->setParameter('label', $label)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByPortal(Portal $portal) {

        return $this->createQueryBuilder('customObject')
            ->where('customObject.portal = :portal')
            ->setParameter('portal', $portal)
            ->orderBy('customObject.label', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $internalName
     * @param Portal $portal
     * @return mixed
     */
    public function findByInternalNameAndPortal($internalName, Portal $portal)
    {
        return $this->createQueryBuilder('customObject')
            ->where('customObject.internalName = :internalName')
            ->andWhere('customObject.portal = :portal')
            ->setParameter('internalName', $internalName)
            ->setParameter('portal', $portal->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $label
     * @param Portal $portal
     * @return mixed
     */
    public function findByLabelAndPortal($label, Portal $portal)
    {
        return $this->createQueryBuilder('customObject')
            ->where('customObject.label = :label')
            ->andWhere('customObject.portal = :portal')
            ->setParameter('label', $label)
            ->setParameter('portal', $portal->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $internalName
     * @param $portalInternalIdentifier
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByInternalNameAndPortalInternalIdentifier($internalName, $portalInternalIdentifier)
    {
        return $this->createQueryBuilder('customObject')
            ->join('customObject.portal', 'portal')
            ->where('customObject.internalName = :internalName')
            ->andWhere('portal.internalIdentifier = :internalIdentifier')
            ->setParameter('internalName', $internalName)
            ->setParameter('internalIdentifier', $portalInternalIdentifier)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
