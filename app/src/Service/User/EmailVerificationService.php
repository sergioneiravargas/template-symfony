<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Service\Notification\Handler as NotificationHandler;
use App\Service\Notification\Request as NotificationRequest;
use App\Service\Notification\Strategy\EmailVerificationStrategy;
use App\Service\User\Exception\InvalidParameterException;
use App\Service\User\Exception\InvalidTokenException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailVerificationService
{
    private const URL_PARTS_SCHEME = 'scheme';
    private const URL_PARTS_HOST = 'host';
    private const URL_PARTS_PATH = 'path';
    private const URL_PARTS_QUERY = 'query';

    public function __construct(
        private EntityManagerInterface $em,
        private NotificationHandler $notificationHandler,
        private UrlGeneratorInterface $urlGeneratorInterface,
        private Security $security,
        private string $baseUrl,
        private string $routeName, // Nombre de la ruta de verificación
        private string $tokenSecret,
        private int $tokenTtl = 1800, // TTL en segundos
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
            throw new \Exception(message: $result->errorMessage);
        }
    }

    public function generateVerificationUrl(User $user): string
    {
        $expireTimestamp = time() + $this->tokenTtl;
        $token = self::generateToken(
            email: $user->getEmail(),
            expireTimestamp: $expireTimestamp,
            secret: $this->tokenSecret,
        );

        $relativePath = $this->urlGeneratorInterface->generate(
            $this->routeName, // Nombre
            [
                'user' => $user->getId(),
                'token' => $token,
                'expire' => $expireTimestamp,
            ],
            UrlGeneratorInterface::RELATIVE_PATH,
        );

        return $this->baseUrl.'/'.$relativePath;
    }

    public function validateVerificationUrl(string $url): void
    {
        // Validar usuario y email
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new InvalidParameterException(message: 'Invalid user');
        }
        $email = $user->getEmail();
        if (!$email) {
            throw new InvalidParameterException(message: 'User doesn\'t have an email');
        }

        // Validar URL
        $urlParts = parse_url($url);
        if (!isset($urlParts[self::URL_PARTS_HOST])) {
            throw new InvalidParameterException(message: 'Missing URL host');
        }

        // Validar host de la URL
        $baseUrlParts = parse_url($this->baseUrl);
        if ($urlParts[self::URL_PARTS_HOST] !== $baseUrlParts[self::URL_PARTS_HOST]) {
            throw new InvalidParameterException(message: 'URL host doesn\'t match');
        }

        // Validar protocolo de la URL
        if (isset($baseUrlParts[self::URL_PARTS_SCHEME])) {
            if (!isset($urlParts[self::URL_PARTS_SCHEME])) {
                throw new InvalidParameterException(message: 'Missing URL scheme');
            }
            if ($urlParts[self::URL_PARTS_SCHEME] !== $baseUrlParts[self::URL_PARTS_SCHEME]) {
                throw new InvalidParameterException(message: 'URL scheme doesn\'t match');
            }
        }

        // Validar path de la URL
        if (isset($baseUrlParts[self::URL_PARTS_PATH])) {
            if (!isset($urlParts[self::URL_PARTS_PATH])) {
                throw new InvalidParameterException(message: 'Missing URL path');
            }
            if ($urlParts[self::URL_PARTS_PATH] !== $baseUrlParts[self::URL_PARTS_PATH]) {
                throw new InvalidParameterException(message: 'URL path doesn\'t match');
            }
        }

        // Validar query params de la URL
        if (!isset($urlParts[self::URL_PARTS_QUERY])) {
            throw new InvalidParameterException(message: 'Missing URL query parameters');
        }
        parse_str($urlParts[self::URL_PARTS_QUERY], $queryParts);

        $token = $queryParts['token'] ?? null;
        $validToken = is_string($token);
        if (!$validToken) {
            throw new InvalidParameterException(message: 'Invalid token');
        }
        $token = (string) $token;

        $expireTimestamp = $queryParts['expire'] ?? null;
        $validExpireTimestamp = is_numeric($expireTimestamp);
        if (!$validExpireTimestamp) {
            throw new InvalidParameterException(message: 'Invalid expire timestamp');
        }
        $expireTimestamp = (int) $expireTimestamp;
        if ($expireTimestamp < time()) {
            throw new InvalidParameterException(message: 'Expired URL');
        }

        // Validar token
        $isValid = self::validateToken(
            token: $token,
            email: $email,
            secret: $this->tokenSecret,
            expireTimestamp: $expireTimestamp,
        );
        if (!$isValid) {
            throw new InvalidTokenException(message: 'Invalid token');
        }

        $user->setVerified(true);
        $this->em->flush();
    }

    public static function generateToken(
        string $email,
        string $secret,
        int $expireTimestamp,
    ): string {
        $password = self::createTokenPassword($email, $secret, $expireTimestamp);

        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function validateToken(
        string $token,
        string $email,
        string $secret,
        int $expireTimestamp,
    ): bool {
        $password = self::createTokenPassword($email, $secret, $expireTimestamp);

        return password_verify($password, $token);
    }

    private static function createTokenPassword(
        string $email,
        string $secret,
        int $expireTimestamp,
    ): string {
        return $email.$secret.$expireTimestamp;
    }
}
