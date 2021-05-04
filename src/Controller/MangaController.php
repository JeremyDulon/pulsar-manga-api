<?php

namespace App\Controller;

use App\Entity\Manga;
use App\Entity\UserMangaPlatform;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class MangaController extends BaseController
{
    // Remake: this
    /**
     * @Rest\Get("/mangas", name="get_mangas")
     * @Rest\View(serializerGroups={"mangaList", "image"})
     *
     * @return array
     */
    public function getMangasAction(): array {
        return $this->em->getRepository(Manga::class)->findBy([]);
    }

    /**
     * @Rest\Get("/manga/{slug}", name="get_manga")
     * @Rest\View(serializerGroups={"mangaList", "image", "mangaData", "platformData", "chapterList"})
     *
     * @ParamConverter(
     *     "manga",
     *     options={"mapping": {"slug": "slug" }}
     * )
     * @param Manga $manga
     * @return array
     */
    public function getMangaAction(Manga $manga): array {
        return [
            'manga' => $manga
        ];
    }
}
