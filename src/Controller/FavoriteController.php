<?php


namespace App\Controller;


use App\Entity\Manga;
use App\Entity\MangaPlatform;
use App\Entity\User;
use App\Entity\UserMangaPlatform;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class FavoriteController extends BaseController
{
    /**
     * @Rest\Get("/favorites", name="get_favorites")
     * @Rest\View(serializerGroups={"mangaList", "image", "mangaData", "platformData", "chapterList"})
     *
     */
    public function getFavoritesAction(): array
    {
        $userMangaPlatforms = $this->em->getRepository(UserMangaPlatform::class)
            ->createQueryBuilder('ump')
            ->select('ump', 'mp', 'm', 'mi')
            ->leftJoin('ump.mangaPlatform', 'mp')
            ->leftJoin('mp.manga', 'm')
            ->leftJoin('m.image', 'mi')
            ->where('ump.user = :user')
            ->setParameter('user', $this->getUser())
            ->getQuery()
            ->getResult();

        return array_map(function (UserMangaPlatform $um) {
            return $um->getMangaPlatform()->getManga();
        }, $userMangaPlatforms);
    }

    /**
     * @Rest\Put("/favorites/add/{mangaPlatformId}", name="add_favorite")
     * @Rest\View(serializerGroups={ "getUser", "getUserManga", "mangaSlug" })
     *
     * @ParamConverter(
     *     "mangaPlatform",
     *     options={"mapping": {"mangaPlatformId": "id" }}
     * )
     * @param MangaPlatform $mangaPlatform
     * @return User|UserInterface|void|null
     * @throws ORMException
     */
    public function addFavoriteAction(MangaPlatform $mangaPlatform) {
        $user = $this->getUser();
        $userMangaPlatform = $user->isFavorite($mangaPlatform);
        if ($userMangaPlatform === false) {
            $userMangaPlatform = new UserMangaPlatform();
            $userMangaPlatform->setMangaPlatform($mangaPlatform)
                ->setUser($user);

            $this->em->persist($userMangaPlatform);
        }
        $userMangaPlatform->setFavorite(!$userMangaPlatform->getFavorite());
        $this->em->flush();
        $this->em->refresh($user);

        return $user;
    }
}
