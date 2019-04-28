<?php

namespace App\Repository;

use App\Entity\Folder;
use App\Entity\Portal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Folder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Folder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Folder[]    findAll()
 * @method Folder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FolderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Folder::class);
    }

    // /**
    //  * @return Folder[] Returns an array of Folder objects
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
    public function findOneBySomeField($value): ?Folder
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
     * @param $folderId
     * @return array
     */
    public function getDataTableData(Portal $portal, $start, $length, $search, $orders, $columns, $folderId)
    {

        // Main Query
        $mainQuerySelectColumns = ['dt.id', 'dt.name', 'dt.createdAt'];
        $searchQuery = null;
        $query = $this->createQueryBuilder('dt')
            ->select($mainQuerySelectColumns)
            ->where('dt.portal = :portal')
            ->andWhere('dt.type = :type')
            ->setParameter('type', Folder::LIST_FOLDER)
            ->setParameter('portal', $portal->getId());

        if(!$folderId) {
            $query->andWhere('dt.parentFolder is NULL');
        } else {
            $query->andWhere('dt.parentFolder = :parentFolder')
                ->setParameter('parentFolder', $folderId);
        }

        // Search
        $searches = [];
        if(!empty($search['value'])) {

            $searchableColumns = ['dt.name'];
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

}
