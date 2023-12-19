<?php

declare(strict_types=1);

namespace App\Security\Exception;

abstract class PublicException extends \Exception
{
    public function __construct(
        string $message,
        int $code,
        ?\Throwable $previous,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
