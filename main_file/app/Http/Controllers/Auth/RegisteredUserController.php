
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function create()
    {
        if (getSettingsValByName('register_page') !== 'on') {
            abort(404);
        }

        return view('auth.register');
    }

    public function store(Request $request)
    {
        $settings = settings();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'agree' => ['accepted'],
        ];

        if (($settings['google_recaptcha'] ?? null) === 'on') {
            $rules['g-recaptcha-response'] = ['required', 'captcha'];
        }

        $validated = $request->validate($rules);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        auth()->login($user);

        // Change 'dashboard' to your intended landing route after signup
        return redirect()->route('dashboard')->with('success', __('Account created successfully!'));
    }
}

