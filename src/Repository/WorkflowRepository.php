<?php

namespace App\Repository;

use App\Entity\Portal;
use App\Entity\Workflow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Workflow|null find($id, $lockMode = null, $lockVersion = null)
 * @method Workflow|null findOneBy(array $criteria, array $orderBy = null)
 * @method Workflow[]    findAll()
 * @method Workflow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkflowRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Workflow::class);
    }

    // /**
    //  * @return Workflow[] Returns an array of Workflow objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Workflow
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param $uid
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getWorkflowAndAssociationsByUid($uid)
    {
        return $this->createQueryBuilder('workflow')
            ->where('workflow.uid = :uid')
            ->setParameter('uid', $uid)
            ->getQuery()
            ->getOneOrNullResult();
    }


    /**
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getWorkflowById($id)
    {
        return $this->createQueryBuilder('workflow')
            ->innerJoin('workflow.triggers', 'triggers')
            ->where('workflow.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Portal $portal
     * @param $start
     * @param $length
     * @param $search
     * @param $orders
     * @param $columns
     * @return array
     */
    public function getDataTableData(Portal $portal, $start, $length, $search, $orders, $columns)
    {
        // Main Query
        $mainQuerySelectColumns = ['dt.id', 'dt.uid', 'dt.name', 'dt.createdAt'];
        $searchQuery = null;
        $query = $this->createQueryBuilder('dt')
            ->select($mainQuerySelectColumns)
            ->where('dt.portal = :portal')
            ->andWhere('dt.draft = 1')
            ->setParameter('portal', $portal->getId());

        // Search
        if(!empty($search['value'])) {
            $searchItem = $search['value'];
            $searchQuery = 'dt.name LIKE \'%'.$searchItem.'%\'';
        }

        if ($searchQuery) {
            $query->andWhere($searchQuery);
        }

        // Limit
        $query->setFirstResult($start)->setMaxResults($length);

        // Order
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

}
