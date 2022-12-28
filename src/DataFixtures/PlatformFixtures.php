<?php

namespace App\DataFixtures;

use App\Entity\Platform;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PlatformFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $platforms = [
            [ 'name' => 'MangaPark', 'baseUrl' => 'https://mangapark.net', 'status' => Platform::STATUS_ENABLED ],
            [ 'name' => 'MangaFast', 'baseUrl' => 'https://mangafast.net', 'status' => Platform::STATUS_DISABLED ],
            [ 'name' => 'FanFox', 'baseUrl' => 'http://fanfox.net', 'status' => Platform::STATUS_ENABLED ],
        ];

        foreach ($platforms as $platform) {
            $platformEntity = new Platform();

            $platformEntity->setName($platform['name'])
                ->setBaseUrl($platform['baseUrl'])
                ->setStatus($platform['status']);

            $manager->persist($platformEntity);
        }

        $manager->flush();
    }
}
