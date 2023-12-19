<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Form\UserPasswordType;
use App\Service\User\EmailVerificationService;
use App\Service\User\Exception\PublicException;
use App\Service\User\PasswordRecoveryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_web_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_web_logout')]
    #[IsGranted('ROLE_USER')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/email-verification', name: 'app_web_email_verification')]
    #[IsGranted('ROLE_USER')]
    public function verifyEmail(
        Request $request,
        EmailVerificationService $emailVerificationService,
    ): Response {
        $url = $request->getUri();
        try {
            $emailVerificationService->validateUrl($url);
            $failed = false;
            $message = 'Your email address has been successfully verified';
        } catch (\Throwable $e) {
            $failed = true;
            $message = $e instanceof PublicException
                ? $e->getMessage()
                : 'An unexpected error occurred while verifying your email address';
        }

        return $this->render('security/task_result.html.twig', [
            'title' => 'Email verification',
            'failed' => $failed,
            'message' => $message,
        ]);
    }

    #[Route('/password-recovery', name: 'app_web_password_recovery')]
    #[IsGranted('ROLE_USER')]
    public function recoverPassword(
        Request $request,
        PasswordRecoveryService $passwordRecoveryService,
    ): Response {
        $url = $request->getUri();
        try {
            $passwordRecoveryService->validateUrl($url);
            $failed = false;
        } catch (\Throwable $e) {
            $failed = true;
            $message = $e instanceof PublicException
                ? $e->getMessage()
                : 'An unexpected error occurred while verifying your email address';

            return $this->render('security/task_result.html.twig', [
                'title' => 'Password recovery',
                'failed' => $failed,
                'message' => $message,
            ]);
        }

        $form = $this->createForm(UserPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->getData()['password'];

            try {
                $passwordRecoveryService->changePassword($url, $plainPassword);
                $failed = false;
                $message = 'Your password has been successfully changed';
            } catch (\Throwable $e) {
                $failed = true;
                $message = $e instanceof PublicException
                    ? $e->getMessage()
                    : 'An unexpected error occurred while verifying your email address';
            }

            return $this->render('security/task_result.html.twig', [
                'title' => 'Password recovery',
                'failed' => $failed,
                'message' => $message,
            ]);
        }

        return $this->render('security/password_recovery.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
