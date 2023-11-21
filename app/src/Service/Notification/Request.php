<?php

namespace App\Service\Notification;

final class Request
{
    public function __construct(
        public readonly string $type,
        public readonly array $data,
    ) {
    }
}
