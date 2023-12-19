<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Security\Exception\PublicException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class SecurityExceptionListener
{
    #[AsEventListener(
        event: KernelEvents::EXCEPTION,
        priority: 2, // the priority must be greater than the Security HTTP ExceptionListener, to make sure it's called before the default exception listener
    )]
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof PublicException) {
            return;
        }
        $response = new Response($exception->getMessage(), $exception->getCode());
        $event->setResponse($response);
    }
}
