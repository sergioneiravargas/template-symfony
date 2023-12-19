<?php

declare(strict_types=1);

namespace App\Service\Notification\Strategy;

use App\Service\Notification\HandlerStrategyInterface;
use App\Service\Notification\Request;
use App\Service\Notification\Result;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailVerificationStrategy implements HandlerStrategyInterface
{
    public const REQUEST_TYPE = 'EMAIL_VERIFICATION';

    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    public function canHandle(Request $request): bool
    {
        return self::REQUEST_TYPE === $request->type;
    }

    public function notify(Request $request): Result
    {
        try {
            $this->validateRequest($request);
        } catch (\Throwable $e) {
            return new Result(
                request: $request,
                isSuccessful: false,
                errorMessage: $e->getMessage(),
                errorTrace: $e->getTraceAsString(),
            );
        }

        $to = $request->data['to'];
        $verificationUrl = $request->data['verificationUrl'];

        $message = new Email();
        $message
            ->to($to)
            ->subject('Email verification')
            ->text('Verify your email address')
            ->html(
                "Enter to the following link to verify your email address: <a href=\"{$verificationUrl}\">link</a>",
            );

        try {
            $this->mailer->send($message);
        } catch (\Throwable $e) {
            return new Result(
                request: $request,
                isSuccessful: false,
                errorMessage: $e->getMessage(),
                errorTrace: $e->getTraceAsString(),
            );
        }

        return new Result(
            request: $request,
            isSuccessful: true,
        );
    }

    private function validateRequest(Request $request): void
    {
        $hasValidData = isset($request->data['to'])
            && is_string($request->data['to'])
            && isset($request->data['verificationUrl'])
            && is_string($request->data['verificationUrl']);
        if (!$hasValidData) {
            throw new \InvalidArgumentException('Invalid request data');
        }
    }
}
