<?php

declare(strict_types=1);

namespace App\Service\Notification;

interface StrategyInterface
{
    public function shouldNotify(Request $request): bool;

    public function notify(Request $request): void;
}
