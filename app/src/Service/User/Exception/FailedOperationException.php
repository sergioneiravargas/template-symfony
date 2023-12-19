<?php

declare(strict_types=1);

namespace App\Service\User\Exception;

class FailedOperationException extends PublicException
{
    public function __construct(
        string $message = 'Operation failed',
        int $code = 0,
        \Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
