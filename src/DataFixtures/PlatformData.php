<?php


namespace App\DataFixtures;


use App\Entity\Platform;
use App\Utils\Platform as UtilsPlatform;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PlatformData extends Fixture
{
    public function load(ObjectManager $manager) {
        foreach (UtilsPlatform::getPlatforms() as $platform) {
            $newPlatform = new Platform();
            $newPlatform->setLanguage($platform['language'] ?? '')
                ->setName($platform['name'] ?? '')
                ->setBaseUrl($platform['baseUrl'] ?? '')
                ->setMangaPath($platform['mangaPath'] ?? '')
                ->setChapterPath($platform['chapterPath'] ?? '');

            $manager->persist($newPlatform);
        }

        $manager->flush();
    }
}
