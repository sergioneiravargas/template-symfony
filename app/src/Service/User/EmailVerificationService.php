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
    public const TASK = 'EMAIL_VERIFICATION';

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
                'verificationUrl' => $this->generateUrl($user),
            ],
        );

        $result = $this->notificationHandler->handleNotification($request);
        if (!$result->isSuccessful) {
            throw new FailedOperationException(message: 'Notification could not be sent');
        }
    }

    public function generateUrl(User $user): string
    {
        return $this->tokenService->generateUrl(
            routeName: $this->routeName,
            task: self::TASK,
            target: $user->getEmail(),
            tokenTtl: $this->tokenTtl,
        );
    }

    public function validateUrl(
        string $url,
        User $user,
    ): void {
        $params = $this->tokenService->getUrlParams($url);
        if (self::TASK !== $params->task) {
            throw new InvalidParameterException(message: 'Invalid task');
        }
        if ($params->target !== $user->getEmail()) {
            throw new InvalidParameterException(message: 'Invalid target');
        }

        $this->tokenService->validateUrlParams($params);

        $user->setVerified(true);
        $this->em->flush();
    }
}
