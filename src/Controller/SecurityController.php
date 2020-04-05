<?php

namespace App\Controller;

use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;
use App\Mailer\ResetPasswordMailer;
use App\Model\ForgotPassword;
use App\Model\ResetPassword;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
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
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var ResetPasswordMailer
     */
    private $resetPasswordMailer;

    /**
     * SecurityController constructor.
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ResetPasswordMailer $resetPasswordMailer
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        ResetPasswordMailer $resetPasswordMailer
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->resetPasswordMailer = $resetPasswordMailer;
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
