<?php

declare(strict_types=1);

namespace App\Service\User\Exception;

class InvalidTokenException extends \Exception
{
    public function __construct(
        string $message = 'Invalid token',
        int $code = 0,
        \Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
