<?php


namespace App\Controller;


use App\Entity\Chapter;
use App\Entity\Manga;
use App\Entity\MangaPlatform;
use App\Entity\User;
use App\Entity\UserMangaPlatform;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class ReadController extends BaseController
{
    /**
     * @Rest\Put("/read/chapter/{chapterId}/page/{page}", name="read_chapter_chapter")
     * @Rest\View(serializerGroups={ "getUser", "getUserManga", "mangaSlug" })
     *
     * @ParamConverter(
     *     "chapter",
     *     options={"mapping": {"chapterId": "id" }}
     * )
     * @param Chapter $chapter
     * @param int $page
     * @return User|UserInterface|void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function readChapterPageAction(Chapter $chapter, int $page)
    {
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
        $userMangaPlatform->setLastPage($page);
        $this->em->flush();

        return $this->getUser();
    }
}
