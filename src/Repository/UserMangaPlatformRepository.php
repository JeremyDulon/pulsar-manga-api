<?php

namespace App\Repository;

use App\Entity\UserMangaPlatform;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserMangaPlatform|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserMangaPlatform|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserMangaPlatform[]    findAll()
 * @method UserMangaPlatform[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserMangaPlatformRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMangaPlatform::class);
    }

    // /**
    //  * @return UserMangaPlatform[] Returns an array of UserMangaPlatform objects
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
    public function findOneBySomeField($value): ?UserMangaPlatform
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
