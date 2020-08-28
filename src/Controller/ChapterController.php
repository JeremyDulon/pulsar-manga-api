<?php


namespace App\Controller;

use App\Entity\Chapter;
use FOS\RestBundle\Controller\Annotations as Rest;

class ChapterController extends BaseController
{
    /**
     * @Rest\Get("/chapter/{id}", name="get_chapter")
     * @Rest\View(serializerGroups={"chapter", "image"})
     *
     * @param Chapter $chapter
     * @return array
     */
    public function getMangaAction(Chapter $chapter): array {
        return [
            'chapter' => $chapter
        ];
    }
}
