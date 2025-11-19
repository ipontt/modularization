<?php

namespace Modules\Payment;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use NumberFormatter;
use RuntimeException;

/**
 * Fake payment provider, for testing purposes
 */
final class PayBuddySDK
{
    /**
     * @param  string  $token  A valid payment token
     * @param  int  $amount  The amount to charge in cents (or the smallest unit or another currency)
     * @param  string  $description  The statement description
     * @return array{id: string, amount: int, localized_amount: string, description: string, created_at: string}
     *
     * @throws RuntimeException
     */
    public function charge(string $token, int $amount, string $description): array
    {
        $this->validateToken($token);

        $formatter = new NumberFormatter('en-US', NumberFormatter::CURRENCY);

        return [
            'id' => Str::uuid7()->toString(),
            'amount' => $amount,
            'localized_amount' => $formatter->format($amount / 100) ?: '',
            'description' => $description,
            'created_at' => Date::now()->toDateTimeString(),
        ];
    }

    public static function make(): PayBuddySDK
    {
        return new self;
    }

    public static function validToken(): string
    {
        return Str::uuid7();
    }

    public static function invalidToken(): string
    {
        return substr(self::validToken(), 0, 35);
    }

    /** @throws RuntimeException */
    protected function validateToken(string $token): void
    {
        if (! Str::isUuid($token)) {
            throw new RuntimeException('The given payment token is not valid.');
        }
    }
}
