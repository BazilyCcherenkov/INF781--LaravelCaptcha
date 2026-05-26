<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class Recaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail('Debes confirmar que no eres un robot.');
            return;
        }

        $response = Http::asForm()->post(
            config('services.recaptcha.verify_url'),
            [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $value,
                'remoteip' => request()->ip(),
            ]
        );

        $data = $response->json();

        if (!($data['success'] ?? false)) {
            $fail('La verificación CAPTCHA falló. Inténtalo de nuevo.');
        }
    }
}