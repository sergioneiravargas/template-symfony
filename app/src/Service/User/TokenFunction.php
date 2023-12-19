<?php

declare(strict_types=1);

namespace App\Service\User;

class TokenFunction
{
    public static function generate(
        string $email,
        string $secret,
        int $expireTimestamp,
    ): string {
        $password = self::createTokenPassword($email, $secret, $expireTimestamp);

        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function validate(
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
