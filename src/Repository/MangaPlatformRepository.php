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

    /**
     * @param string $title
     * @param array $altTitles
     * @param Platform $platform
     * @return Manga
     * @throws NonUniqueResultException
     */
    public function findOneByNames(string $title, array $altTitles, Platform $platform) {
        $qb = $this->createQueryBuilder('mp')
            ->select('mp')
            ->leftJoin('mp.manga','m')->addSelect('m')
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

        return $qb->andWhere('mp.platform = :platform')
            ->setParameter('platform', $platform)
            ->getQuery()
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
