<?php

namespace App\DataFixtures;

use App\Entity\Portal;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PortalFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $portal = new Portal();
        $portal->setInternalIdentifier('9874561920')
            ->setName('Southeast')
            ->setSystemDefined(true);

        $manager->persist($portal);
        $this->addReference('portal_southeast', $portal);
        $manager->flush();
    }
}
