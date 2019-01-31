<?php

namespace App\Repository;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Model\FieldCatalog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Property|null find($id, $lockMode = null, $lockVersion = null)
 * @method Property|null findOneBy(array $criteria, array $orderBy = null)
 * @method Property[]    findAll()
 * @method Property[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PropertyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Property::class);
    }

    // /**
    //  * @return Property[] Returns an array of Property objects
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
    public function findOneBySomeField($value): ?Property
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
    public function findByCustomObject(CustomObject $customObject)
    {
        return $this->createQueryBuilder('property')
            ->where('property.customObject = :customObject')
            ->setParameter('customObject', $customObject->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param CustomObject $customObject
     * @return mixed
     */
    public function getDataTableData(CustomObject $customObject)
    {

        return $this->createQueryBuilder('property')
            ->innerJoin('property.propertyGroup', 'propertyGroup')
            ->andWhere('property.customObject = :customObject')
            ->setParameter('customObject', $customObject->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $internalName
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByInternalName($internalName)
    {
        return $this->createQueryBuilder('property')
            ->where('property.internalName = :internalName')
            ->setParameter('internalName', $internalName)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param CustomObject $customObject
     * @return mixed
     */
    public function findAllInternalNamesAndLabelsForCustomObject(CustomObject $customObject)
    {
        return $this->createQueryBuilder('property')
            ->select('property.internalName, property.label')
            ->innerJoin('property.customObject', 'customObject')
            ->where('customObject = :customObject')
            ->setParameter('customObject', $customObject)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param CustomObject $customObject
     * @param $fieldType
     * @return mixed
     */
    public function findAllInternalNamesByFieldTypeForCustomObject(CustomObject $customObject, $fieldType)
    {
        return $this->createQueryBuilder('property')
            ->select('property.internalName')
            ->innerJoin('property.customObject', 'customObject')
            ->where('customObject = :customObject')
            ->andWhere('property.fieldType = :fieldType')
            ->setParameter('customObject', $customObject)
            ->setParameter('fieldType', $fieldType)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param PropertyGroup $propertyGroup
     * @return mixed
     */
    public function findAllInternalNamesForPropertiesByPropertyGroup(PropertyGroup $propertyGroup)
    {
        return $this->createQueryBuilder('property')
            ->select('property.internalName')
            ->innerJoin('property.propertyGroup', 'propertyGroup')
            ->where('propertyGroup = :propertyGroup')
            ->setParameter('propertyGroup', $propertyGroup)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param $label
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByLabel($label)
    {
        return $this->createQueryBuilder('property')
            ->where('property.label = :label')
            ->setParameter('label', $label)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $label
     * @param CustomObject $customObject
     * @return mixed
     */
    public function findByLabelAndCustomObject($label, CustomObject $customObject)
    {
        return $this->createQueryBuilder('property')
            ->where('property.label = :label')
            ->andWhere('property.customObject = :customObject')
            ->setParameter('label', $label)
            ->setParameter('customObject', $customObject->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $internalName
     * @param CustomObject $customObject
     * @return mixed
     */
    public function findByInternalNameAndCustomObject($internalName, CustomObject $customObject)
    {
        return $this->createQueryBuilder('property')
            ->where('property.internalName = :internalName')
            ->andWhere('property.customObject = :customObject')
            ->setParameter('internalName', $internalName)
            ->setParameter('customObject', $customObject->getId())
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByArrayOfIds($ids)
    {
        return $this->createQueryBuilder('property')
            ->where('property.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Portal $portal
     * @param CustomObject|null $customObjectReference
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSelectizeSearchResultProperties(Portal $portal, CustomObject $customObjectReference) {

        $fieldTypes = [FieldCatalog::SINGLE_LINE_TEXT, FieldCatalog::MULTI_LINE_TEXT];
        $queryBuilder = $this->createQueryBuilder('property')
            ->innerJoin('property.customObject', 'customObject')
            ->where('customObject.portal = :portal')
            ->andWhere('customObject = :customObject')
            ->andWhere('property.fieldType IN (:fieldTypes)')
            ->setParameter('fieldTypes', $fieldTypes)
            ->setParameter('portal', $portal)
            ->setParameter('customObject', $customObjectReference);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param CustomObject $customObject
     * @return mixed
     */
    public function findColumnsForTable(CustomObject $customObject)
    {
        return $this->createQueryBuilder('property')
            ->where('property.customObject = :customObject')
            ->andWhere('property.isColumn = :bool')
            ->setParameter('bool', true)
            ->setParameter('customObject', $customObject->getId())
            ->orderBy('property.columnOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getHighestColumnOrder(CustomObject $customObject) {
        return $this->createQueryBuilder('property')
            ->select('max(property.columnOrder) as column_order')
            ->where('property.customObject = :customObject')
            ->andWhere('property.isColumn = :bool')
            ->setParameter('bool', true)
            ->setParameter('customObject', $customObject->getId())
            ->orderBy('property.columnOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getCountWherePropertyIsColumn(CustomObject $customObject) {
        return $this->createQueryBuilder('property')
            ->select('count(property.id) as count')
            ->where('property.customObject = :customObject')
            ->andWhere('property.isColumn = :bool')
            ->setParameter('bool', true)
            ->setParameter('customObject', $customObject->getId())
            ->orderBy('property.columnOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param PropertyGroup $propertyGroup
     * @return mixed
     */
    public function getCountByPropertyGroup(PropertyGroup $propertyGroup)
    {
        return $this->createQueryBuilder('property')
            ->select('count(property.id) as count')
            ->join('property.propertyGroup', 'propertyGroup')
            ->where('propertyGroup = :propertyGroup')
            ->setParameter('propertyGroup', $propertyGroup->getId())
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
        return $this->createQueryBuilder('property')
            ->join('property.customObject', 'customObject')
            ->join('customObject.portal', 'portal')
            ->where('property.internalName = :internalName')
            ->andWhere('portal.internalIdentifier = :internalIdentifier')
            ->setParameter('internalName', $internalName)
            ->setParameter('internalIdentifier', $portalInternalIdentifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
