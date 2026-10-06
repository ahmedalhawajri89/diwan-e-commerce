<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reads Arabic-Indic digits as the digits they are.
 *
 * An Arabic keyboard types ٣٥٠ for 350. Validation saw letters, the integer
 * rule refused the room width, and a phone number was stored in a form the
 * courier's dialler may not take. Every submitted string has its digits
 * converted to Western ones before anything reads it.
 *
 * Passwords are left exactly as typed: a password is a secret, not a number,
 * and changing its characters would change the password.
 */
class WesternDigits
{
    private const FROM = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    private const TO = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    private const UNTOUCHED = ['password', 'password_confirmation', 'current_password'];

    public function handle(Request $request, Closure $next): Response
    {
        $request->merge($this->convert($request->except(self::UNTOUCHED)));

        return $next($request);
    }

    private function convert(array $input): array
    {
        return array_map(
            fn ($value) => match (true) {
                is_array($value) => $this->convert($value),
                is_string($value) => str_replace(self::FROM, self::TO, $value),
                default => $value,
            },
            $input
        );
    }
}
