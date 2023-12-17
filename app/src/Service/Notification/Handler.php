<?php

declare(strict_types=1);

namespace App\Service\Notification;

class Handler
{
    /**
     * @var HandlerStrategyInterface[]
     */
    private $strategies = [];

    public function addStrategy(HandlerStrategyInterface $strategy)
    {
        $this->strategies[] = $strategy;
    }

    public function handleNotification(Request $request): Result
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->canHandle($request)) {
                return $strategy->notify($request);
            }
        }

        return new Result(
            request: $request,
            isSuccessful: false,
            errorMessage: 'No strategy found for this request.',
        );
    }
}
