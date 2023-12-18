<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Service\User\EmailVerificationService;
use App\Service\User\Exception\InvalidParameterException;
use App\Service\User\Exception\InvalidTokenException;
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
            $emailVerificationService->validateVerificationUrl($url);
            $message = 'Your email address has been successfully verified';
            $failed = false;
        } catch (InvalidParameterException $e) {
            $failed = true;
            $message = $e->getMessage();
        } catch (InvalidTokenException $e) {
            $failed = true;
            $message = $e->getMessage();
        } catch (\Throwable $e) {
            $failed = true;
            $message = 'An unexpected error occurred while verifying your email address';
        }

        return $this->render('security/email_verification.html.twig', [
            'message' => $message,
            'failed' => $failed,
        ]);
    }
}
