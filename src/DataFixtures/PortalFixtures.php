<?php

namespace App\DataFixtures;

use App\Entity\Portal;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class PortalFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $portal = new Portal();
        $portal->setInternalIdentifier('9874561920');

        $manager->persist($portal);
        $this->addReference('portal_1', $portal);

        $manager->flush();

    }
}
