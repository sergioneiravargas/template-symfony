<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Service\Notification\Handler as NotificationHandler;
use App\Service\Notification\Request as NotificationRequest;
use App\Service\Notification\Strategy\EmailVerificationStrategy;
use App\Service\User\Exception\FailedOperationException;
use App\Service\User\Exception\InvalidParameterException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class EmailVerificationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private NotificationHandler $notificationHandler,
        private Security $security,
        private TokenService $tokenService,
        private string $routeName, // Nombre de la ruta de verificación
        private int $tokenTtl, // Tiempo de vida del token
    ) {
    }

    public function sendNotification(User $user): void
    {
        $request = new NotificationRequest(
            type: EmailVerificationStrategy::REQUEST_TYPE,
            data: [
                'to' => $user->getEmail(),
                'verificationUrl' => $this->generateVerificationUrl($user),
            ],
        );

        $result = $this->notificationHandler->handleNotification($request);
        if (!$result->isSuccessful) {
            throw new FailedOperationException(message: 'Notification could not be sent');
        }
    }

    public function generateVerificationUrl(User $user): string
    {
        return $this->tokenService->generateUrl($user, $this->routeName, $this->tokenTtl);
    }

    public function validateVerificationUrl(string $url): void
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new InvalidParameterException(message: 'Invalid user');
        }

        $this->tokenService->validateUrl($user, $url);

        $user->setVerified(true);
        $this->em->flush();
    }
}
