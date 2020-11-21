<?php

namespace App\Repository;

use App\Entity\UserManga;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserManga|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserManga|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserManga[]    findAll()
 * @method UserManga[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserMangaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserManga::class);
    }

    // /**
    //  * @return UserManga[] Returns an array of UserManga objects
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
    public function findOneBySomeField($value): ?UserManga
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
