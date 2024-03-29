<?php

namespace App\Repository;

use App\Entity\CustomObject;
use App\Entity\PropertyGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PropertyGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method PropertyGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method PropertyGroup[]    findAll()
 * @method PropertyGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PropertyGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PropertyGroup::class);
    }

    // /**
    //  * @return PropertyGroup[] Returns an array of PropertyGroup objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PropertyGroup
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param CustomObject $customObject
     * @return mixed
     */
    public function getCountByCustomObject(CustomObject $customObject)
    {
        return $this->createQueryBuilder('propertyGroup')
            ->select('count(propertyGroup.id) as count')
            ->join('propertyGroup.customObject', 'customObject')
            ->where('customObject = :customObject')
            ->setParameter('customObject', $customObject->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param CustomObject $customObject
     * @return mixed
     */
    public function getPropertyGroupsAndProperties(CustomObject $customObject)
    {

        return $this->createQueryBuilder('propertyGroup')
            ->leftJoin('propertyGroup.properties', 'properties')
            ->where('propertyGroup.customObject = :customObject')
            ->setParameter('customObject', $customObject->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param CustomObject $customObject
     * @return mixed
     */
    public function getPropertiesForCustomObject(CustomObject $customObject)
    {

        return $this->createQueryBuilder('propertyGroup')
            ->leftJoin('propertyGroup.properties', 'properties')
            ->where('propertyGroup.customObject = :customObject')
            ->setParameter('customObject', $customObject->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param CustomObject $customObject
     * @return mixed
     */
    public function getDefaultPropertyData(CustomObject $customObject)
    {

        return $this->createQueryBuilder('propertyGroup')
            ->leftJoin('propertyGroup.properties', 'properties')
            ->where('propertyGroup.customObject = :customObject')
            ->setParameter('customObject', $customObject->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $name
     * @param CustomObject $customObject
     * @return mixed
     */
    public function findByNameAndCustomObject($name, CustomObject $customObject)
    {
        return $this->createQueryBuilder('propertyGroup')
            ->where('propertyGroup.name = :name')
            ->andWhere('propertyGroup.customObject = :customObject')
            ->setParameter('name', $name)
            ->setParameter('customObject', $customObject->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $internalName
     * @param $portalInternalIdentifier
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByInternalNameAndPortalInternalIdentifierAndCustomObjectInternalName($internalName, $portalInternalIdentifier, $customObjectInternalName)
    {
        return $this->createQueryBuilder('propertyGroup')
            ->join('propertyGroup.customObject', 'customObject')
            ->join('customObject.portal', 'portal')
            ->where('propertyGroup.internalName = :internalName')
            ->andWhere('portal.internalIdentifier = :internalIdentifier')
            ->andWhere('customObject.internalName = :customObjectInternalName')
            ->setParameter('internalName', $internalName)
            ->setParameter('internalIdentifier', $portalInternalIdentifier)
            ->setParameter('customObjectInternalName', $customObjectInternalName)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
