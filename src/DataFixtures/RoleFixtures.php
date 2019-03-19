<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RoleFixtures extends Fixture implements DependentFixtureInterface
{

    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $role = new Role();
        $role->setName('ALL');
        $role->setObjectPermissions(['ALL']);
        $role->setSystemPermissions(['ALL']);
        $role->setPortal($this->getReference('portal_1'));

        $this->addReference('role_1', $role);

        $manager->persist($role);

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
            PortalFixtures::class
        );
    }
}
