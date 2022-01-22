<?php

namespace App\Repository;

use App\Entity\Chapter;
use App\Entity\ComicIssue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ComicIssue|null find($id, $lockMode = null, $lockVersion = null)
 * @method ComicIssue|null findOneBy(array $criteria, array $orderBy = null)
 * @method ComicIssue[]    findAll()
 * @method ComicIssue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComicIssueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ComicIssue::class);
    }

    /**
     * @param int $comicId
     * @param int $number
     * @return int|mixed|string|null
     * @throws NonUniqueResultException
     */
    public function findNextComicIssue(int $comicId, int $number) {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.manga', 'mp')
            ->andWhere('mp.id = :comicId')
            ->setParameter('comicId', $comicId)
            ->andWhere('c.number = :number')
            ->setParameter('number', ++$number)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $comicId
     * @param int $number
     * @return array|float|int|string
     */
    public function getNextAndPreviousComicIssues(int $comicId, int $number) {
        return $this->createQueryBuilder('c')
            ->select('c.id, c.number')
            ->leftJoin('c.manga', 'mp')
            ->andWhere('mp.id = :comicId')
            ->setParameter('comicId', $comicId)
            ->andWhere('c.number = :numberUp OR c.number = :numberDown')
            ->setParameter('numberUp', $number + 1)
            ->setParameter('numberDown', $number - 1)
            ->orderBy('c.number', 'asc')
            ->getQuery()
            ->getArrayResult();
    }
}
