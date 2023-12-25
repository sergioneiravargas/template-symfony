<?php

declare(strict_types=1);

namespace App\Service\Notification\Strategy;

use App\Service\Notification\Request;
use App\Service\Notification\Result;
use App\Service\Notification\StrategyInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class PasswordRecoveryStrategy implements StrategyInterface
{
    public const REQUEST_TYPE = 'PASSWORD_RECOVERY';

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
        $recoveryUrl = $request->data['recoveryUrl'];

        $message = new Email();
        $message
            ->to($to)
            ->subject('Password recovery')
            ->text('Recover your password')
            ->html(
                "Enter to the following link to recover your password: <a href=\"{$recoveryUrl}\">link</a>",
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
            && isset($request->data['recoveryUrl'])
            && is_string($request->data['recoveryUrl']);
    }
}
