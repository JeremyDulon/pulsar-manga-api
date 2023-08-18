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
    public function findNextComicIssue(ComicIssue $comicIssue) {
        return $this->createQueryBuilder('ci')
            ->where('ci.comicLanguage = :comicLanguage')
            ->setParameter('comicLanguage', $comicIssue->getComicLanguage())
            ->andWhere('ci.number = :number')
            ->setParameter('number', $comicIssue->getNumber() + 1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getComicIssuesBySlugAndLanguage(string $slug, string $language)
    {
        return $this->createQueryBuilder('ci')
            ->innerJoin('ci.comicLanguage', 'cl')
            ->innerJoin('cl.comic', 'c')
            ->where('cl.language = :language')
            ->andWhere('c.slug = :slug')
            ->setParameters([
                'language' => $language,
                'slug' => $slug
            ])
            ->getQuery()
            ->getResult();
    }
}
