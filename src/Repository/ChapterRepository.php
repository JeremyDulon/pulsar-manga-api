<?php

namespace App\Repository;

use App\Entity\Chapter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Chapter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chapter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chapter[]    findAll()
 * @method Chapter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChapterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chapter::class);
    }

    /**
     * @param $mangaId
     * @param $number
     * @return int|mixed|string|null
     * @throws NonUniqueResultException
     */
    public function findNextChapter($mangaId, $number) {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.manga', 'mp')
            ->andWhere('mp.id = :mangaId')
            ->setParameter('mangaId', $mangaId)
            ->andWhere('c.number = :number')
            ->setParameter('number', ++$number)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getNextAndPreviousChapters($mangaId, $number) {
        return $this->createQueryBuilder('c')
            ->select('c.id, c.number')
            ->leftJoin('c.manga', 'mp')
            ->andWhere('mp.id = :mangaId')
            ->setParameter('mangaId', $mangaId)
            ->andWhere('c.number = :numberUp OR c.number = :numberDown')
            ->setParameter('numberUp', $number + 1)
            ->setParameter('numberDown', $number - 1)
            ->orderBy('c.number', 'asc')
            ->getQuery()
            ->getArrayResult();
    }

    // /**
    //  * @return Chapter[] Returns an array of Chapter objects
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
    public function findOneBySomeField($value): ?Chapter
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
