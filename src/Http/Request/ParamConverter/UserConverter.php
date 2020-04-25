<?php

namespace App\Http\Request\ParamConverter;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;


class UserConverter implements ParamConverterInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UserConverter constructor.
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     */
    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request $request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $userId = $request->attributes->get('userId');

        $user = $this->userRepository->find($userId);

        if(!$user) {
            return false;
        }

        $request->attributes->set($configuration->getName(), $user);

        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {

        if($configuration->getClass() !== User::class) {
            return false;
        }

        return true;
    }
}