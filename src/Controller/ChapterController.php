<?php


namespace App\Controller;

use App\Entity\Chapter;
use App\Entity\UserMangaPlatform;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

class ChapterController extends BaseController
{
    // Remake: this
    /**
     * @Rest\Get("/chapter/{id}", name="get_chapter")
     * @Rest\View(serializerGroups={"chapter", "image"})
     *
     * @param Chapter $chapter
     * @return array
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function getChapterAction(Chapter $chapter): array {
        $this->updateUserLastChapter($chapter);

        $chaptersArr = $this->em->getRepository(Chapter::class)->getNextAndPreviousChapters($chapter->getMangaId(), $chapter->getNumber());
        foreach ($chaptersArr as $chapterArr) {
            if ($chapterArr['number'] > $chapter->getNumber()) {
                $chapter->setNextChapterId($chapterArr['id']);
            } else {
                $chapter->setPreviousChapterId($chapterArr['id']);
            }
        }

        return [
            'chapter' => $chapter
        ];
    }

    /**
     * @Rest\Get("/chapter/{mangaId}/{number}/next", name="get_next_chapter")
     * @Rest\View(serializerGroups={"chapter", "image"})
     *
     * @Entity("chapter", expr="repository.findNextChapter(mangaId, number)")
     * @param Chapter $chapter
     * @return array
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function getNextChapterAction(Chapter $chapter): array {
        $this->updateUserLastChapter($chapter);

        return [
            'chapter' => $chapter
        ];
    }

    /**
     * @param Chapter $chapter
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateUserLastChapter(Chapter $chapter) {
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
    }
}
