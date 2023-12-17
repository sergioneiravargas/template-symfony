<?php

declare(strict_types=1);

namespace App\Service\Notification;

interface HandlerStrategyInterface
{
    public function canHandle(Request $request): bool;

    public function notify(Request $request): Result;
}
