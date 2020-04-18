<?php

namespace App\Controller;

use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;
use App\Model\ForgotPassword;
use App\Model\ResetPassword;
use App\Utils\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use Auth0\SDK\Helpers\JWKFetcher;
use Auth0\SDK\Helpers\Tokens\AsymmetricVerifier;
use Auth0\SDK\Helpers\Tokens\SymmetricVerifier;
use Auth0\SDK\Helpers\Tokens\IdTokenVerifier;

class SecurityController extends AbstractController
{
    use ServiceHelper;

    /**
     * @Route("/login-auth0", name="login_auth0", options = { "expose" = true })
     * @param Request $request
     * @return Response
     */
    public function loginAuth0(Request $request): Response
    {
        // todo setup the database connection in the env file
        return $this->redirect($this->auth0Authenticator->getLoginUrl());
    }

    /**
     * @Route("/logout-auth0", name="logout_auth0", options = { "expose" = true })
     * @param Request $request
     * @return Response
     * @throws \Auth0\SDK\Exception\CoreException
     */
    public function logoutAuth0(Request $request): Response
    {
        // todo setup the database connection in the env file
        $this->auth0Authenticator->logout();
        return $this->redirect($this->auth0Authenticator->getLogoutLink());
    }

    /**
     * @Route("/auth0-callback", name="auth0_callback", options = { "expose" = true })
     * @param Request $request
     * @return Response
     * @throws \Auth0\SDK\Exception\CoreException
     */
    public function auth0Callback(Request $request): Response
    {

        return new JsonResponse([
           'access_token' => $request->request->get('access_token'),
            'id_token' => $request->request->get('id_token')
        ]);

        // todo this will be a route on the front end picked up by react.js in the near future

        // todo grab the access token here and make requests with it in postman.
/*        $name = "josh";
        if(!$request->request->has('id_token')) {
            die( 'No `id_token` URL parameter' );
        }

        $id_token  = rawurldecode($request->request->get('id_token'));

        $token_issuer  = 'https://'.getenv('AUTH0_DOMAIN').'/';
        $signature_verifier = null;

        $jwks_fetcher = new JWKFetcher();
        $jwks        = $jwks_fetcher->getKeys($token_issuer.'.well-known/jwks.json');
        $signature_verifier = new AsymmetricVerifier($jwks);

        $token_verifier = new IdTokenVerifier(
            $token_issuer,
            getenv('AUTH0_CLIENT_ID'),
            $signature_verifier
        );

        try {
            $decoded_token = $token_verifier->verify($id_token);
            echo '<pre>'.print_r($decoded_token, true).'</pre>';
        } catch (\Exception $e) {
            echo 'Caught: Exception - '.$e->getMessage();
        }

        // do nothing as this will be picked up by the Auth0Authenticator

        return new Response("success parsing token");*/
    }

    /**
     * @Route("/sign-up", name="sign_up", methods={"GET"}, options = { "expose" = true })
     */
    public function signUp(AuthenticationUtils $authenticationUtils)
    {
        return new Response("sign up");
    }

    /**
     * @Route("/login", name="app_login", options = { "expose" = true })
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $resetPassword = new ForgotPassword();

        $form = $this->createForm(ForgotPasswordType::class, $resetPassword);

        return $this->render('security/login.html.twig', ['form' => $form->createView(), 'last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/login-form", name="login_form", methods={"GET"}, options = { "expose" = true })
     */
    public function getLoginForm(AuthenticationUtils $authenticationUtils): JsonResponse
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $formMarkup = $this->renderView(
            'Api/form/login_form.html.twig', []
        );

        return new JsonResponse(
            [
                'success' => true,
                'formMarkup' => $formMarkup
            ],
            Response::HTTP_OK
        );

    }

    /**
     * @Route("reset-password/{token}", name="reset_password", methods={"GET"}, requirements={"token" = "^[a-f0-9]{64}$"})
     *
     * @param Request $request
     * @param string $token
     * @return Response
     */
    public function resetPasswordAction(Request $request, $token)
    {
        return $this->render('security/reset-password.html.twig', []);
    }

    /**
     * @Route("/reset-password-form/{token}", name="reset_password_form", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function resetPasswordFormAction(Request $request, $token): Response
    {

        $user = $this->userRepository->getByPasswordResetToken($token);

        if(!$user) {

            return new JsonResponse(
                [
                    'success' => false,
                    'message' => 'Invalid or expired password reset request'
                ], Response::HTTP_BAD_REQUEST
            );

        }

        $resetPassword = new ResetPassword();

        $form = $this->createForm(ResetPasswordType::class, $resetPassword, [
            'action' => $this->generateUrl('reset_password_form', ['token' => $token]),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        $formMarkup = $this->renderView(
            'Api/form/reset_password_form.html.twig', ['form' => $form->createView()]
        );

        if ($form->isSubmitted()) {

            if (!$form->isValid()) {

                return new JsonResponse(
                    [
                        'success' => false,
                        'formMarkup' => $formMarkup
                    ], Response::HTTP_BAD_REQUEST
                );

            } else {

                /** @var ResetPassword $resetPassword */
                $resetPassword = $form->getData();

                $user->setPassword($this->passwordEncoder->encodePassword(
                    $user,
                    $resetPassword->getPassword()
                ));

                $user->clearPasswordResetToken();

                $this->entityManager->persist($user);
                $this->entityManager->flush();

            }
        }

        return new JsonResponse(
            [
                'success' => true,
                'formMarkup' => $formMarkup
            ],
            Response::HTTP_OK
        );

    }

    /**
     * @Route("/forgot-password", name="forgot_password", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Request $request
     * @return Response
     */
    public function forgotPasswordAction(Request $request): Response
    {
        return $this->render('security/login.html.twig', []);
    }

    /**
     * @Route("/forgot-password-form", name="forgot_password_form", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function forgotPasswordFormAction(Request $request): Response
    {
        $forgotPassword = new ForgotPassword();

        $form = $this->createForm(ForgotPasswordType::class, $forgotPassword, [
            'action' => $this->generateUrl('forgot_password_form'),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        $formMarkup = $this->renderView(
            'Api/form/forgot_password_form.html.twig', ['form' => $form->createView()]
        );

        if ($form->isSubmitted()) {

            if(!$form->isValid()) {

                return new JsonResponse(
                    [
                        'success' => false,
                        'formMarkup' => $formMarkup,
                    ], Response::HTTP_BAD_REQUEST
                );

            } else {

                /** @var ForgotPassword $forgotPassword */
                $forgotPassword = $form->getData();
                $emailAddress = $forgotPassword->getEmailAddress();

                $user = $this->userRepository->getByEmailAddress($emailAddress);

                // If the forgot-email function was used within the last 24 hours for
                // this user, render the form with an appropriate validation message.
                $currentTokenTimestamp = $user->getPasswordResetTokenTimestamp();
                if ($currentTokenTimestamp && $currentTokenTimestamp >= new \DateTime('-23 hours 59 minutes 59 seconds')) {
                    $errorMessage = 'Sorry, an email containing password reset instructions has been sent to this email address within the last 24 hours';
                    $form->addError(new FormError($errorMessage));

                    $formMarkup = $this->renderView(
                        'Api/form/forgot_password_form.html.twig', ['form' => $form->createView()]
                    );

                    return new JsonResponse(
                        [
                            'success' => false,
                            'formMarkup' => $formMarkup,
                        ], Response::HTTP_BAD_REQUEST
                    );
                }

                $user->setPasswordResetToken();

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->resetPasswordMailer->send($user);

                return new JsonResponse(
                    [
                        'success' => true,
                        'message' => sprintf("An email containing instructions for resetting your password has been sent to: %s", $emailAddress),
                        'formMarkup' => $formMarkup
                    ],
                    Response::HTTP_OK
                );

            }
        }

        return new JsonResponse(
            [
                'success' => true,
                'formMarkup' => $formMarkup
            ],
            Response::HTTP_OK
        );
    }
}
