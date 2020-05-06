<?php

namespace App\Dto\DataTransformer;

use App\Dto\CustomObject_Dto;
use App\Dto\User_Dto;
use App\Entity\CustomObject;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class User_DtoTransformer implements DataTransformerInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * User_DtoTransformer constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function transform($user)
    {
        if(!$user instanceof User) {
            throw new TransformationFailedException(sprintf("Object to transform must be an instance of %s", User::class));
        }

        return (new User_Dto())
            ->setId($user->getId())
            ->setEmail($user->getEmail())
            ->setFirstName($user->getFirstName())
            ->setLastName($user->getLastName());
    }

    public function reverseTransform($dto)
    {
        if(!$dto instanceof User_Dto) {
            throw new TransformationFailedException(sprintf("Dto must be an instance of %s", User_Dto::class));
        }

        if($dto->getId()) {
            $user = $this->userRepository->find($dto->getId());
            if(!$user) {
                throw new TransformationFailedException('User with dto id not found');
            }
        } else {
            $user = new User();
        }

        $user->setEmail($dto->getEmail())
            ->setFirstName($dto->getFirstName())
            ->setLastName($dto->getLastName());
        // todo add password here as well

        return $user;
    }
}