<?php

declare(strict_types=1);

namespace App\Service\Notification\Strategy;

use App\Service\Notification\Exception\FailedNotification;
use App\Service\Notification\Exception\InvalidRequest;
use App\Service\Notification\Request;
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

    public function notify(Request $request): void
    {
        if (!$this->requestIsValid($request)) {
            throw new InvalidRequest();
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
        } catch (\Throwable $th) {
            throw new FailedNotification(message: $th->getMessage(), code: $th->getCode(), previous: $th);
        }
    }

    private function requestIsValid(Request $request): bool
    {
        return isset($request->data['to'])
            && is_string($request->data['to'])
            && isset($request->data['verificationUrl'])
            && is_string($request->data['verificationUrl']);
    }
}
