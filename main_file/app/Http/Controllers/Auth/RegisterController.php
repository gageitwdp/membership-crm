
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     * Adjust this to wherever you want new users to land.
     */
    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the registration form view.
     */
    public function showRegistrationForm()
    {
        // Respect the "register_page" setting toggle if present
        if (function_exists('getSettingsValByName') && getSettingsValByName('register_page') !== 'on') {
            abort(404);
        }

        return view('auth.register');
    }

    /**
     * Get a validator for an incoming registration request.
     * Matches your Blade fields (name, email, password, password_confirmation, agree).
     * Enforces reCAPTCHA when settings()['google_recaptcha'] == 'on'.
     */
    protected function validator(array $data)
    {
        $settings = function_exists('settings') ? settings() : [];

        $rules = [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'confirmed', 'min:8'],
            'agree'                 => ['accepted'], // require Terms acceptance
        ];

        if (!empty($settings['google_recaptcha']) && $settings['google_recaptcha'] === 'on') {
            // Provided by your NoCaptcha package (e.g., anhskohbo/no-captcha)
            $rules['g-recaptcha-response'] = ['required', 'captcha'];
        }

        return Validator::make($data, $rules);
    }

    /**
     * Create a new user instance after successful validation.
     */
    protected function create(array $data)
    {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Handle the registration request.
     * Uses the RegistersUsers trait convention but allows custom flashes/redirects if desired.
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        // If you want email verification, fire the event and redirect to notice (see optional section below)
        // event(new \Illuminate\Auth\Events\Registered($user));

        $this->guard()->login($user);

        return redirect($this->redirectPath())->with('success', __('Account created successfully!'));
    }
}

