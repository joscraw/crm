<?php

namespace App\DataFixtures;

use App\Entity\Property;
use App\Model\FieldCatalog;
use App\Model\SingleLineTextField;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PropertyFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /*$property = new Property();
        $property->setInternalName('first_name');
        $property->setLabel('First Name');
        $property->setRequired(true);
        $property->setFieldType(FieldCatalog::SINGLE_LINE_TEXT);
        $singleLineTextField = new SingleLineTextField();
        $property->setField($singleLineTextField);
        $property->setCustomObject($this->getReference('custom_object_1'));
        $property->setPropertyGroup($this->getReference('property_group_1'));
        $property->setSystemDefined(true);

        $manager->persist($property);

        $property = new Property();
        $property->setInternalName('last_name');
        $property->setLabel('Last Name');
        $property->setRequired(true);
        $property->setFieldType(FieldCatalog::SINGLE_LINE_TEXT);
        $singleLineTextField = new SingleLineTextField();
        $property->setField($singleLineTextField);
        $property->setCustomObject($this->getReference('custom_object_1'));
        $property->setPropertyGroup($this->getReference('property_group_1'));
        $property->setSystemDefined(true);

        $manager->persist($property);

        $property = new Property();
        $property->setInternalName('email');
        $property->setIsUnique(true);
        $property->setLabel('Email');
        $property->setRequired(true);
        $property->setFieldType(FieldCatalog::SINGLE_LINE_TEXT);
        $singleLineTextField = new SingleLineTextField();
        $property->setField($singleLineTextField);
        $property->setCustomObject($this->getReference('custom_object_1'));
        $property->setPropertyGroup($this->getReference('property_group_1'));
        $property->setSystemDefined(true);

        $manager->persist($property);

        $manager->flush();*/
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
            /*PropertyGroupFixtures::class,
            CustomObjectFixtures::class,*/
        );
    }
}
