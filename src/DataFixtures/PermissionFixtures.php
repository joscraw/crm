<?php

namespace App\DataFixtures;

use App\Entity\Folder;
use App\Entity\Portal;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PermissionFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {




        $portal = new Portal();
        $portal->setInternalIdentifier('9874561920');

        $manager->persist($portal);
        $this->addReference('portal_1', $portal);

        $f1 = new Folder();
        $f1->setType('LIST');
        $f1->setName('Mailchimp');
        $f1->setPortal($portal);
        $f2 = new Folder();
        $f2->setType('LIST');
        $f2->setName('Salesforce');
        $f2->setParentFolder($f1);
        $f2->setPortal($portal);

        $manager->persist($f1);
        $manager->persist($f2);

        $manager->flush();

    }
}
