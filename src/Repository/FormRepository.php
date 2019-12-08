<?php

namespace App\Repository;

use App\Entity\Form;
use App\Entity\Portal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Form|null find($id, $lockMode = null, $lockVersion = null)
 * @method Form|null findOneBy(array $criteria, array $orderBy = null)
 * @method Form[]    findAll()
 * @method Form[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Form::class);
    }

    // /**
    //  * @return Form[] Returns an array of Form objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Form
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

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
        $mainQuerySelectColumns = ['dt.id', 'dt.uid', 'dt.name', 'dt.createdAt', 'dt.submissionCount'];
        $searchQuery = null;
        $query = $this->createQueryBuilder('dt')
            ->select($mainQuerySelectColumns)
            ->where('dt.portal = :portal')
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

    /**
     * @param Portal $portal
     * @return mixed
     */
    public function getTotalCount(Portal $portal)
    {

        $query = $this->createQueryBuilder('form')
            ->where('form.portal = :portal')
            ->setParameter('portal', $portal->getId());

        return count($query->getQuery()->getResult());

    }
}
