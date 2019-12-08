<?php

namespace App\Repository;

use App\Entity\MarketingList;
use App\Entity\Portal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MarketingList|null find($id, $lockMode = null, $lockVersion = null)
 * @method MarketingList|null findOneBy(array $criteria, array $orderBy = null)
 * @method MarketingList[]    findAll()
 * @method MarketingList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarketingListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarketingList::class);
    }

    // /**
    //  * @return MarketingList[] Returns an array of MarketingList objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MarketingList
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
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
     * @param null $folderId
     * @return array
     */
    public function getDataTableData(Portal $portal, $start, $length, $search, $orders, $columns)
    {
        // Main Query
        $mainQuerySelectColumns = ['dt.id', 'dt.name', 'dt.createdAt', 'dt.type'];
        $searchQuery = null;
        $query = $this->createQueryBuilder('dt')
            ->select($mainQuerySelectColumns)
            ->where('dt.portal = :portal')
            ->setParameter('portal', $portal->getId());

        // Search
        $searches = [];
        if(!empty($search['value'])) {

            $searchableColumns = ['dt.name', 'dt.type'];
            $searchItem = $search['value'];

            foreach($searchableColumns as $searchableColumn) {

                $searches[] = sprintf('LOWER(%s) LIKE \'%%%s%%\'', $searchableColumn, strtolower($searchItem));

            }
        }

        $i = 0;
        foreach($searches as $search) {

            if($i === 0) {
                $query->andWhere($search);
            } else {
                $query->orWhere($search);
            }

            $i++;
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
     * @param $start
     * @param $length
     * @param $search
     * @param $orders
     * @param $columns
     * @param null $folderId
     * @return array
     */
    public function getDataTableDataForFolder(Portal $portal, $start, $length, $search, $orders, $columns, $folderId = null)
    {
        // Main Query
        $mainQuerySelectColumns = ['dt.id', 'dt.name', 'dt.createdAt', 'dt.type'];
        $searchQuery = null;
        $query = $this->createQueryBuilder('dt')
            ->select($mainQuerySelectColumns)
            ->where('dt.portal = :portal')
            ->setParameter('portal', $portal->getId());

        if(!$folderId) {
            $query->andWhere('dt.folder is NULL');

        } else {
            $query->andWhere('dt.folder = :folder')
                ->setParameter('folder', $folderId);
        }

        // Search
        $searches = [];
        if(!empty($search['value'])) {

            $searchableColumns = ['dt.name', 'dt.type'];
            $searchItem = $search['value'];

            foreach($searchableColumns as $searchableColumn) {

                $searches[] = sprintf('LOWER(%s) LIKE \'%%%s%%\'', $searchableColumn, strtolower($searchItem));

            }
        }

        /**
         * @see https://symfonycasts.com/screencast/doctrine-queries/and-where-or-where
         */
        if(!empty($searches)) {
            $query->andWhere(implode(" OR ", $searches));
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

        $query = $this->createQueryBuilder('list')
            ->where('list.portal = :portal')
            ->setParameter('portal', $portal->getId());

        return count($query->getQuery()->getResult());

    }
}
