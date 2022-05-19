<?php

namespace App\Repository;

use App\Entity\ComicIssueRead;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ComicIssueRead|null find($id, $lockMode = null, $lockVersion = null)
 * @method ComicIssueRead|null findOneBy(array $criteria, array $orderBy = null)
 * @method ComicIssueRead[]    findAll()
 * @method ComicIssueRead[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComicIssueReadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ComicIssueRead::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(ComicIssueRead $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(ComicIssueRead $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return ComicIssueRead[] Returns an array of ComicIssueRead objects
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
    public function findOneBySomeField($value): ?ComicIssueRead
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
