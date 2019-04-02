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
     * Fetch a user entity by email address
     *
     * @param string $emailAddress
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByEmailAddress($emailAddress) {
        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $emailAddress)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $token
     * @return User|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function getByPasswordResetToken($token) {
        return $this->createQueryBuilder('u')
            ->where('u.passwordResetToken = :token')
            ->setParameter('token', $token)
            ->andWhere('u.passwordResetTokenTimestamp >= :timestamp')
            ->setParameter('timestamp', new \DateTime('-23 hours 59 minutes 59 seconds'))
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
     * @param $customFilters
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getDataTableData(Portal $portal, $start, $length, $search, $orders, $columns, $customFilters)
    {

        // filters
        $filters = [];
        foreach ($customFilters as $key => $filter) {

            // We need to setup the alias and column name for any join tables
            switch($filter['name']) {
                case 'custom_roles':
                    $filter['name'] = 'name';
                    $alias = 'r';
                    break;
                default:
                    $alias = 'u';
                    break;
            }

            $filters[] = $this->getCondition($filter, $alias);

        }

        $filterString = implode(" AND ", $filters);
        $filterString = empty($filters) ? '' : "AND $filterString";

        // Search
        $searches = [];
        if(!empty($search['value'])) {
            $searchItem = $search['value'];

            foreach($columns as $column) {

                // We need to setup the alias and column name for any join tables
                switch($column['name']) {
                    case 'custom_roles':
                        $alias = 'r';
                        $column['name'] = 'name';
                        break;
                    default:
                        $alias = 'u';
                        break;
                }

                $searches[] = sprintf('LOWER(%s.%s) LIKE \'%%%s%%\'', $alias, $column['name'], strtolower($searchItem));
            }

        }

        $searchString = implode(" OR ", $searches);
        $searchString = empty($searches) ? '' : "AND $searchString";

        // Main Query
        $query = sprintf("SELECT u.id, u.email, u.first_name, u.last_name, u.is_active, u.is_admin_user, GROUP_CONCAT(r.name SEPARATOR ', ') as custom_roles from user u left join user_role ur on u.id = ur.user_id left join role r on r.id = ur.role_id WHERE 1=1 %s %s GROUP BY u.id", $filterString, $searchString);

        // Order
        foreach ($orders as $key => $order) {
            // Orders does not contain the name of the column, but its number,
            // so add the name so we can handle it just like the $columns array
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }

        foreach ($orders as $key => $order) {

            if(isset($order['name'])) {
                $query .= " ORDER BY {$order['name']}";
            }

            $query .= ' ' . $order['dir'];
        }

        // limit
        $query .= sprintf(' LIMIT %s, %s', $start, $length);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return array(
            "results"  => $results
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

    /**
     * @param $customFilter
     * @param $alias
     * @return string
     */
    private function getCondition($customFilter, $alias) {

        $query = '';
        switch($customFilter['fieldType']) {
            case 'single_line_text_field':
                switch($customFilter['operator']) {
                    case 'EQ':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('%s.%s = \'\'', $alias, $customFilter['name']);
                        } else {
                            $query = sprintf('LOWER(%s.%s) LIKE \'%%%s%%\'', $alias, $customFilter['name'], strtolower($customFilter['value']));
                        }

                        break;
                    case 'NEQ':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('%s.%s != \'\'', $alias, $customFilter['name']);
                        } else {
                            $query = sprintf('LOWER(%s.%s) NOT LIKE \'%%%s%%\'', $alias, $customFilter['name'], strtolower($customFilter['value']));
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf('%s.%s is not null', $alias, $customFilter['name']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf('%s.%s is null', $alias, $customFilter['name']);

                        break;
                }
                break;
            case 'single_checkbox_field':

                switch($customFilter['operator']) {
                    case 'IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('%s.%s = \'\'', $alias, $customFilter['name']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            if($values == ['0','1']) {
                                $query = sprintf('%s.%s = \'%s\' OR %s.%s = \'%s\'', $alias, $customFilter['name'], '1', $alias, $customFilter['name'], 0);
                            } elseif ($values == ['0']) {
                                $query = sprintf('%s.%s = \'%s\'', $alias, $customFilter['name'], '0');
                            } elseif ($values == ['1']) {
                                $query = sprintf('%s.%s = \'%s\'', $alias, $customFilter['name'], '1');
                            }
                        }

                        break;
                    case 'NOT_IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('%s.%s != \'\'', $alias, $customFilter['name']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            if($values == ['0','1']) {
                                $query = sprintf('%s.%s != \'%s\' AND %s.%s != \'%s\'', $alias, $customFilter['name'], '1', $alias, $customFilter['name'], 0);
                            } elseif ($values == ['0']) {
                                $query = sprintf('%s.%s != \'%s\'', $alias, $customFilter['name'], '0');
                            } elseif ($values == ['1']) {
                                $query = sprintf('%s.%s != \'%s\'', $alias, $customFilter['name'], '1');
                            }
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf('%s.%s is not null', $alias, $customFilter['name']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf('%s.%s is null', $alias, $customFilter['name']);

                        break;

                }
                break;
            case 'multiple_checkbox_field':

                switch($customFilter['operator']) {
                    case 'IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('%s.%s = \'\'', $alias, $customFilter['name']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf('LOWER(%s.%s) = \'%s\'', $alias, $customFilter['name'], strtolower($value));
                            }

                            $query = implode(" OR ", $conditions);
                        }

                        break;
                    case 'NOT_IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('%s.%s != \'\'', $alias, $customFilter['name']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf('LOWER(%s.%s) != \'%s\'', $alias, $customFilter['name'], strtolower($value));
                            }

                            $query = implode(" AND ", $conditions);
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf('%s.%s is not null', $alias, $customFilter['name']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf('%s.%s is null', $alias, $customFilter['name']);

                        break;
                }
                break;
        }

        return $query;
    }
}
