<?php

declare(strict_types=1);

namespace App\Security\Exception;

class DisabledUserException extends PublicException
{
    public function __construct(
        \Throwable $previous = null,
    ) {
        parent::__construct('User has been disabled', 403, $previous);
    }
}
