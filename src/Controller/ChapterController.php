<?php


namespace App\Controller;

use App\Entity\Chapter;
use App\Entity\UserMangaPlatform;
use Doctrine\ORM\Query\Expr\Join;
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
    public function getChapterAction(Chapter $chapter): array {
        $user = $this->getUser();
        $mangaPlatform = $chapter->getManga();
        $userMangaPlatform = $this->em->getRepository(UserMangaPlatform::class)->findOneBy([
            'user' => $user,
            'mangaPlatform' => $mangaPlatform
        ]);

        if (!$userMangaPlatform) {
            $userMangaPlatform = (new UserMangaPlatform())
                ->setUser($user)
                ->setMangaPlatform($mangaPlatform);
        }

        $userMangaPlatform->setLastChapter($chapter);
        $this->em->flush();

        return [
            'chapter' => $chapter
        ];
    }
}
