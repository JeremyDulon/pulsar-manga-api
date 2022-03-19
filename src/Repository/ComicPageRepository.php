<?php

namespace App\Repository;

use App\Entity\ComicPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ComicPage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ComicPage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ComicPage[]    findAll()
 * @method ComicPage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComicPageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ComicPage::class);
    }

    // /**
    //  * @return ChapterPage[] Returns an array of ChapterPage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ChapterPage
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
