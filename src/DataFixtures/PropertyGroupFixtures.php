<?php

namespace App\DataFixtures;

use App\Entity\PropertyGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class PropertyGroupFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $propertyGroup = new PropertyGroup();
        $propertyGroup->setInternalName('general_information');
        $propertyGroup->setName('General Information');
        $propertyGroup->getCustomObject();
        $propertyGroup->setCustomObject($this->getReference('custom_object_1'));
        $propertyGroup->setSystemDefined(true);
        $manager->persist($propertyGroup);

        $this->addReference('property_group_1', $propertyGroup);

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
            CustomObjectFixtures::class,
        );
    }
}
