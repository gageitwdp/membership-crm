<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles, Notifiable, Impersonate;

    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'phone_number',
        'profile',
        'lang',
        'subscription',
        'subscription_expire_date',
        'parent_id',
        'is_active',
        'twofa_secret',
        'first_name',
        'last_name',
        'role',
        'status',
        'terms_accepted_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        // Do NOT cast here
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'subscription_expire_date' => 'datetime', // optional but useful
        'is_active' => 'boolean',                 // if column is tinyint/bool
    ];

    public function canImpersonate()
    {
        // Example: Only super admins can impersonate others
        return $this->type === 'super admin';
    }

    // ... (keep the rest of your methods unchanged)
}
