<?php


namespace App\Controller;


use App\Entity\Manga;
use App\Entity\User;
use App\Entity\UserManga;
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
    public function getFavoritesAction() {
        $userMangas = $this->em->getRepository(UserManga::class)
            ->createQueryBuilder('um')
            ->select('um', 'm', 'mi')
            ->leftJoin('um.manga', 'm')
            ->leftJoin('m.image', 'mi')
            ->where('um.user = :user')
            ->setParameter('user', $this->getUser())
            ->getQuery()
            ->getResult();

        return array_map(function (UserManga $um) {
            return $um->getManga();
        }, $userMangas);
    }

    /**
     * @Rest\Get("/favorites/add/{slug}", name="add_favorite")
     * @Rest\View(serializerGroups={ "getUser", "getUserManga", "mangaSlug" })
     *
     * @ParamConverter(
     *     "manga",
     *     options={"mapping": {"slug": "slug" }}
     * )
     * @param Manga $manga
     * @return User|UserInterface|void|null
     * @throws ORMException
     */
    public function addFavoriteAction(Manga $manga) {
        $user = $this->getUser();
        $userManga = $user->isFavorite($manga);
        if ($userManga === false) {
            $userManga = new UserManga();
            $userManga->setManga($manga)
                ->setUser($user);

            $this->em->persist($userManga);
        } else {
            $user->removeUserManga($userManga);
        }
        $this->em->flush();
        $this->em->refresh($user);

        return $user;
    }
}
