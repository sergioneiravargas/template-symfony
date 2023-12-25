<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Notification\Handler as NotificationHandler;
use App\Service\Notification\Request as NotificationRequest;
use App\Service\Notification\Strategy\EmailVerificationStrategy;
use App\Service\User\Exception\FailedOperationException;
use App\Service\User\Exception\InvalidParameterException;
use Doctrine\ORM\EntityManagerInterface;

class EmailVerificationService
{
    public const TASK = 'EMAIL_VERIFICATION';

    public function __construct(
        private EntityManagerInterface $em,
        private NotificationHandler $notificationHandler,
        private TokenService $tokenService,
        private UserRepository $userRepository,
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

        foreach ($this->notificationHandler->handleNotification($request) as $result) {
            if (!$result->isSuccessful) {
                throw new FailedOperationException(message: 'Notification could not be sent');
            }
        }
    }

    public function generateUrl(User $user): string
    {
        return $this->tokenService->generateUrl(
            routeName: $this->routeName,
            task: $this->getTask($user),
            target: $user->getEmail(),
            tokenTtl: $this->tokenTtl,
        );
    }

    public function verifyEmail(string $url): void
    {
        $params = $this->tokenService->getUrlParams($url);

        $this->validateUrlParams($params);

        $user = $this->userRepository->findOneBy(['email' => $params->target]);
        if (!$user instanceof User) {
            throw new InvalidParameterException(message: 'User not found');
        }

        $user->setVerified(true);
        $this->em->flush();
    }

    private function validateUrlParams(TokenUrlParams $params): void
    {
        $user = $this->userRepository->findOneBy(['email' => $params->target]);
        if (!$user instanceof User) {
            throw new InvalidParameterException(message: 'Invalid target');
        }
        if ($this->getTask($user) !== $params->task) {
            throw new InvalidParameterException(message: 'Invalid task');
        }

        $this->tokenService->validateUrlParams($params);
    }

    private function getTask(User $user): string
    {
        return self::TASK.'_'.$user->getVerifiedAt()?->getTimestamp();
    }
}
