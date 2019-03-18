<?php

namespace App\Repository;

use App\Entity\Portal;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
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
        $mainQuerySelectColumns = ['dt.id', 'dt.firstName', 'dt.lastName', 'dt.email'];
        $searchQuery = null;
        $query = $this->createQueryBuilder('dt')
            ->select($mainQuerySelectColumns)
            ->where('dt.portal = :portal')
            ->setParameter('portal', $portal->getId());

        // Search
        if(!empty($search['value'])) {
            $searchItem = $search['value'];

            $likes = [];

            foreach($columns as $column) {
                $likes[] = sprintf('dt.%s LIKE \'%%'.$searchItem.'%%\'', $column['data']);
            }


            $searchQuery = implode(" OR ", $likes);
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

        $query = $this->createQueryBuilder('user')
            ->where('user.portal = :portal')
            ->setParameter('portal', $portal->getId());

        return count($query->getQuery()->getResult());

    }
}
