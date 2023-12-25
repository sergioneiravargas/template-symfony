<?php

declare(strict_types=1);

namespace App\Service\Notification\Strategy;

use App\Service\Notification\Request;
use App\Service\Notification\Result;
use App\Service\Notification\StrategyInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailVerificationStrategy implements StrategyInterface
{
    public const REQUEST_TYPE = 'EMAIL_VERIFICATION';

    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    public function shouldNotify(Request $request): bool
    {
        return self::REQUEST_TYPE === $request->type;
    }

    public function notify(Request $request): Result
    {
        if (!$this->requestIsValid($request)) {
            return new Result(
                request: $request,
                isSuccessful: false,
                errorMessage: 'Invalid request',
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

    private function requestIsValid(Request $request): bool
    {
        return isset($request->data['to'])
            && is_string($request->data['to'])
            && isset($request->data['verificationUrl'])
            && is_string($request->data['verificationUrl']);
    }
}
