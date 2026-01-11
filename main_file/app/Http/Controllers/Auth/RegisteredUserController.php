
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request)
    {
        // Do NOT read type/role from the request.
        // Force all self-signups to be members.
        $user = User::create([
            'name'              => $request->name,            // if you collect 'name'
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'first_name'        => $request->first_name ?? null,
            'last_name'         => $request->last_name ?? null,
            'type'              => 'member',                  // ← critical
            'role'              => 'member',                  // optional (string field)
            'status'            => 'active',                  // optional
            'is_active'         => 1,                         // optional if you use it
            'terms_accepted_at' => now(),
            // If you need tenant/parent scoping, set parent_id here (e.g., from app settings).
            // 'parent_id'      => $someTenantId,
        ]);

        // Apply Spatie role (uses HasRoles). Ensures permission guard is 'web'.
        // Make sure the 'member' role exists (see seeder section below).
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('member');
        }

        // Trigger Laravel's built-in email verification
        event(new Registered($user));

        return redirect('/main_file/login')
            ->with('status', __('Registration successful. Please verify your email.'));
    }
}
