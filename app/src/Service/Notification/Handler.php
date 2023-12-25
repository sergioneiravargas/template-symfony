<?php

declare(strict_types=1);

namespace App\Service\Notification;

class Handler
{
    /**
     * @var StrategyInterface[]
     */
    private array $strategies = [];

    public function addStrategy(StrategyInterface $strategy): void
    {
        $this->strategies[] = $strategy;
    }

    /**
     * @return Result[]
     */
    public function handleNotification(Request $request): array
    {
        $results = [];
        foreach ($this->strategies as $strategy) {
            if (!$strategy->shouldNotify($request)) {
                continue;
            }

            $results[] = $strategy->notify($request);
        }

        return $results;
    }
}
