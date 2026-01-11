<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Registration is guest-only; route middleware controls access.
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['nullable', 'string', 'max:255'],
            'email'        => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password'     => ['required', 'string', 'min:12'],
            'password_confirmation' => ['required', 'same:password'],
            'first_name'   => ['nullable', 'string', 'max:100'],
            'last_name'    => ['nullable', 'string', 'max:100'],
            // Your Blade uses "acceptTerms" (and previously "agree"):
            'acceptTerms'  => ['accepted'],
            // If reCAPTCHA is enabled on the register page, add this rule and a custom validator:
            // 'g-recaptcha-response' => ['required', new \App\Rules\Recaptcha],
        ];
    }

    public function messages(): array
    {
        return [
            'acceptTerms.accepted' => __('You must accept the Terms and Conditions.'),
        ];
    }
}
