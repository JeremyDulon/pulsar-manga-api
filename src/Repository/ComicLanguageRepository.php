<?php

namespace App\Repository;

use App\Entity\ComicLanguage;
use App\Entity\ComicPlatform;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ComicLanguage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ComicLanguage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ComicLanguage[]    findAll()
 * @method ComicLanguage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComicLanguageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ComicLanguage::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneBySlugAndLanguage(string $slug, string $language): ?ComicLanguage
    {
        return $this->createQueryBuilder('cl')
            ->innerJoin('cl.comic', 'c')
            ->where('cl.language = :language')
            ->andWhere('c.slug = :slug')
            ->setParameters([
                'language' => $language,
                'slug' => $slug
            ])
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
