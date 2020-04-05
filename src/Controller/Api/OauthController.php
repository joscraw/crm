<?php

namespace App\Controller\Api;

use App\Entity\GmailAccount;
use App\Entity\User;
use App\Utils\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ConversationController
 * @package App\Controller
 *
 * @Route("/api/oauth")
 *
 */
class OauthController extends AbstractController
{
    use ServiceHelper;

    /**
     * How to fetch a new refresh token: https://stackoverflow.com/questions/10827920/not-receiving-google-oauth-refresh-token
     * @see https://github.com/googleapis/google-api-php-client/blob/master/examples/simple-file-upload.php
     * @Route("/google-authorization", name="oauth_google_authorization", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function googleAuthorization(Request $request) {
        $googleClient = $this->gmailProvider->getGoogleClient();
        // you must set the access type and approval prompt for the oauth redirect to return a refresh token
        $googleClient->setAccessType('offline');
        $googleClient->setApprovalPrompt('force');
        return $this->redirect($this->gmailProvider->getAuthUrl());
    }

    /**
     * @see https://github.com/googleapis/google-api-php-client/blob/master/examples/simple-file-upload.php
     * @Route("/google-redirect-code", name="oauth_google_redirect_code", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    public function googleRedirectCode(Request $request, SessionInterface $session) {
        if($request->query->has('code')) {
            $token = $this->gmailProvider->getGoogleClient()->fetchAccessTokenWithAuthCode($request->query->get('code'));
            $session->set('api_token', $token);
        }
        return $this->render('oauth/google_redirect.html.twig', array());
    }

    /**
     * This is required to run after the oauth redirecdt due to the fact that we can't
     * get the user from the session after the redirect on /google-redirect-code without
     * rendering a page from the browser and then redirecting back to a route on the server
     * That's what this route is for.
     *
     * @see https://github.com/googleapis/google-api-php-client/blob/master/examples/simple-file-upload.php
     * @Route("/google-after-redirect-code", name="oauth_google_after_redirect_code", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    public function googleAfterRedirectCode(Request $request, SessionInterface $session) {
        //todo we need to try and store the access and refresh token on the user object or on the portal object.
        /** @var User $user */
        $user = $this->getUser();
        if($session->has('api_token')) {
            $portal = $user->getPortal();
            $token = $session->get('api_token');
            $threads = $this->gmailProvider->getThreadList($portal, $token, 1);
            // setup the base gmail object along with the current history ID
            if(!empty($threads)) {
                $gmail = $this->gmailRepository->findOneBy([
                    'portal' => $portal
                ]);
                if(!$gmail) {
                    $gmail = new GmailAccount();
                    $gmail->setGoogleToken($token);
                    $gmail->setPortal($portal);
                }
                $gmail->setCurrentHistoryId($threads[0]['historyId']);
                $this->entityManager->persist($gmail);
            }
            $this->entityManager->persist($portal);
            $this->entityManager->flush();
            return $this->redirectToRoute('conversation_index', ['internalIdentifier' => $portal->getInternalIdentifier()]);
        }
        throw new NotFoundHttpException("Api token from oauth was not found in the session. Try to reauthenticate");
    }
}