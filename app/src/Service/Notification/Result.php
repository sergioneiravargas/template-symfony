<?php

declare(strict_types=1);

namespace App\Service\Notification;

final class Result
{
    public function __construct(
        public readonly Request $request,
        public readonly bool $isSuccessful,
        public readonly ?string $errorMessage = null,
        public readonly ?string $errorTrace = null,
    ) {
    }
}
