<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;

class ClientData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $oauth2Client = new Client();

        $oauth2Client->setId(1);
        $oauth2Client->setRandomId('5w8zrdasdafr4tregd454cw0c0kswcgs0oks40s');
        $oauth2Client->setRedirectUris(array());
        $oauth2Client->setSecret('sdgggskokererg4232404gc4csdgfdsgf8s8ck5s');
        $oauth2Client->setAllowedGrantTypes(array('password', 'refresh_token'));

        $manager->persist($oauth2Client);

        /** @var ClassMetadata $metadata */
        $metadata = $manager->getClassMetadata(get_class($oauth2Client));

        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());

        $manager->flush();
    }
}
