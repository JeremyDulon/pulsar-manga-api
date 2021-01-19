<?php

namespace App\Repository;

use App\Entity\Manga;
use App\Entity\MangaPlatform;
use App\Entity\Platform;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MangaPlatform|null find($id, $lockMode = null, $lockVersion = null)
 * @method MangaPlatform|null findOneBy(array $criteria, array $orderBy = null)
 * @method MangaPlatform[]    findAll()
 * @method MangaPlatform[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MangaPlatformRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MangaPlatform::class);
    }

    // /**
    //  * @return Manga[] Returns an array of Manga objects
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
    public function findOneBySomeField($value): ?Manga
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
