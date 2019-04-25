<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{

    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();

        $user->setEmail('jcrawmer@edoutcome.com');
        $user->setFirstName('Josh');
        $user->setLastName('Crawmer');
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'Eoc123!'
        ));

        $user->setPortal($this->getReference('portal_1'));
        $user->addCustomRole($this->getReference('role_1'));
        $user->setIsActive(true);
        $user->setIsAdminUser(true);
        $user->setRoles(['ROLE_ADMIN_USER']);

        $manager->persist($user);

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
            RoleFixtures::class
        );
    }
}
