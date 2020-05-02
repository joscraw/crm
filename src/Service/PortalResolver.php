<?php

namespace App\Service;


use App\Entity\User;
use App\Repository\PortalRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

class PortalResolver
{

    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var PortalRepository
     */
    private $portalRepository;
    /**
     * @var Security
     */
    private $security;

    public function __construct(RequestStack $requestStack, PortalRepository $portalRepository, Security $security)
    {
        $this->requestStack = $requestStack;
        $this->portalRepository = $portalRepository;
        $this->security = $security;
    }

    public function resolve() {

        if($this->requestStack->getCurrentRequest()->query->has('internalIdentifier')) {

            $internalIdentifier = $this->requestStack->getCurrentRequest()->query->get('internalIdentifier');

            $portal = $this->portalRepository->findOneBy([
                'internalIdentifier' => $internalIdentifier
            ]);

            if($portal) {
                return $portal;
            }

            throw new NotFoundHttpException(sprintf("Portal not found for internal identifier: %s.", $internalIdentifier));

        }

        /**
         * @var User $user
         */
        $user = $this->security->getUser();

        if($user instanceof User) {
            return $user->getPortal();
        }

        throw new AccessDeniedHttpException("Portal not found for the user. 
        Make sure you are logged in with a valid access token or the portal internal identifier is in the query parameter.");
    }

}