<?php

namespace App\DataFixtures;

use App\Entity\CustomObject;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CustomObjectFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $customObject = new CustomObject();
        $customObject->setInternalName('contacts');
        $customObject->setLabel('Contact');
        $customObject->setPortal($this->getReference('portal_1'));

        $this->addReference('custom_object_1', $customObject);

        $manager->persist($customObject);

        $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return array(
            PortalFixtures::class,
        );
    }
}
