<?php

namespace App\Controller\Api;

use App\Entity\ApiToken;
use App\Utils\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

/**
 * Class OrganizationController
 * @package App\Controller
 * @Route("/api")
 */
class TokenController extends AbstractController
{
    use ServiceHelper;

    /**
     * @Route("/tokens", name="new_token", methods={"POST"}, options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function newTokenAction(Request $request) {

        $data = json_decode($request->getContent(), true);
        $username = $data['username'];
        $password = $data['password'];

        $user = $this->userRepository->findOneBy([
            'email' => $username
        ]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $isValid = $this->passwordEncoder->isPasswordValid($user, $password);

        if (!$isValid) {
            throw new BadCredentialsException();
        }

        $apiToken = new ApiToken($user);
        $this->entityManager->persist($apiToken);
        $this->entityManager->flush();

        return new JsonResponse([
            'token' => $apiToken->getToken(),
            'expires_at' => $apiToken->getExpiresAt(),
            'success' => true,
            ]
        );
    }

    /**
     * @Route("/tokens/refresh", name="token_refresh", methods={"POST"}, options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function tokenRefreshAction(Request $request) {

        $token = $request->request->get('token');

        $token = $this->apiTokenRepo->findOneBy([
            'token' => $token
        ]);

        if (!$token) {
            throw new CustomUserMessageAuthenticationException(
                'Invalid API Token'
            );
        }

        $apiToken = new ApiToken($token->getUser());
        $this->entityManager->persist($apiToken);
        $this->entityManager->flush();

        return new JsonResponse([
            'token' => $apiToken->getToken(),
            'expires_at' => $apiToken->getExpiresAt(),
            'success' => true,
            ]
        );
    }
}