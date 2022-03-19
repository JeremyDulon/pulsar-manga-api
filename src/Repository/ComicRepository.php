<?php

namespace App\Repository;

use App\Entity\Comic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comic|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comic|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comic[]    findAll()
 * @method Comic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comic::class);
    }


    /**
     * @param string $title
     * @param array $altTitles
     * @return Comic|null
     * @throws NonUniqueResultException
     */
    public function findOneByAltTitles(string $title, array $altTitles): ?Comic
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m')
            ->where('m.title LIKE :title OR m.title IN (:altTitles)')
            ->setParameter('title', $title)
            ->setParameter('altTitles', $altTitles);

        foreach ($altTitles as $key => $altTitle) {
            $altTitleKey = 'altTitle_'.$key;
            $altTitleLikeKey = 'altTitleLike_'.$key;
            $qb
                ->orWhere("m.title LIKE :$altTitleKey OR m.altTitles LIKE :$altTitleLikeKey")
                ->setParameter($altTitleKey, $altTitle)
                ->setParameter($altTitleLikeKey, '%'.$altTitle.'%');
        }

        return $qb->getQuery()
            ->getOneOrNullResult();
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
