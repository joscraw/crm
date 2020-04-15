<?php

namespace App\Service;

use App\Repository\UserRepository;
use Auth0\SDK\Store\StoreInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

class SessionStore implements StoreInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * SessionStore constructor.
     * @param SessionInterface $session
     * @param TokenStorageInterface $tokenStorage
     * @param UserRepository $userRepository
     */
    public function __construct(
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        UserRepository $userRepository
    ) {
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->userRepository = $userRepository;
    }

    public function set($key, $value)
    {
        if(empty($value)) {
            return;
        }

        $user = $this->userRepository->findOneBy([
            'email' => $value['email']
        ]);

        if(!$user) {
            // create user if it doesn't exist
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
        $this->session->set('_security_main', serialize($token));
    }

    public function get($key, $default = null)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if (!$user instanceof UserInterface) {
            return null;
        }

        // todo maybe add some logic to check if the token provider key matches $key in the future
        return $this->tokenStorage->getToken()->getUser();
    }

    public function delete($key)
    {
        $this->tokenStorage->setToken(null);
        $this->session->invalidate();
    }
}