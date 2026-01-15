# Membership CRM AI Coding Agent Instructions

## Architecture Overview

This is a **Laravel 9** multi-tenant membership CRM with subscription-based access control. Key architectural patterns:

- **Multi-tenancy via `parentId()`**: All data is scoped by `parent_id` using the global `parentId()` helper (see [app/Helper/helper.php](app/Helper/helper.php)). This returns the owner/super admin ID or the user's parent ID.
- **User hierarchy**: `super admin` → `owner` → regular users. Owners are tenants who manage their own members and data.
- **Subscription limits**: Users are constrained by their subscription plan (member limits, user limits, membership plan limits) stored in the `subscriptions` table.

## Critical Patterns & Conventions

### 1. Data Scoping (Multi-tenancy)
**Always filter queries by `parentId()`** when dealing with tenant-specific data:

```php
$members = Member::where('parent_id', parentId())->get();
$roles = Role::where('parent_id', parentId())->get();
```

When creating records, set `parent_id`:
```php
$expense->parent_id = parentId();
```

### 2. Global Helper Functions
Custom helpers in [app/Helper/helper.php](app/Helper/helper.php) (auto-loaded via composer.json):

- `parentId()`: Returns the current tenant owner ID (critical for data isolation)
- `settings()`: Fetches tenant-specific settings from `settings` table
- `settingsKeys()`: Defines default settings structure
- `assignSubscription($id)`: Assigns subscription and calculates expiry dates
- `lastMembershipPlan()`: Gets current user's latest membership

### 3. Settings Management
Settings are **per-tenant** and stored in DB. Access via `settings()` which returns an array:

```php
$details = settings();
$appName = $details['app_name'];
$logo = $details['company_logo'];
```

### 4. Model Relationships
- **Member** → **Membership** (hasOne latest): `$member->membershipLates()`
- **Membership** → **MembershipPlan**: `$membership->plans()`
- **Membership** → **MembershipPayment** (latest): `$membership->latestPayment()`
- **User** → **Subscription**: `$user->subscriptions()`

### 5. Membership Expiry Logic
Membership duration is calculated in models/controllers based on plan duration:
- `Monthly` → +1 month
- `3-Month` → +3 months
- `6-Month` → +6 months
- `Yearly` → +1 year

Use Carbon for date calculations: `Carbon::now()->addMonths(3)->format('Y-m-d')`

## Key Features & Integration Points

### Authentication & Security
- **2FA Support**: Custom middleware [Verify2FA](app/Http/Middleware/Verify2FA.php) checks `twofa_secret` and redirects to OTP page
- **Role-based permissions**: Uses Spatie Laravel Permission package, roles filtered by `parent_id`
- **User Impersonation**: Super admins can impersonate owners via `lab404/laravel-impersonate`

### Payment Integration
- **Stripe**: `stripe/stripe-php` for card payments
- **PayPal**: `srmklive/paypal` for PayPal payments
- Payment methods: Bank Transfer, Stripe, PayPal (see [MembershipPayment](app/Models/MembershipPayment.php))

### Other Integrations
- **Twilio**: SMS notifications (`twilio/sdk`)
- **ReCAPTCHA**: `anhskohbo/no-captcha` (configured via settings)
- **Google 2FA**: `pragmarx/google2fa-laravel` for two-factor auth

## Development Workflow

### Build & Asset Compilation
```bash
npm run dev          # Development build with Laravel Mix
npm run watch        # Watch mode for live reloading
npm run production   # Optimized production build
```

Frontend stack: **TailwindCSS 3** + **Alpine.js** + **Laravel Mix**

### Artisan Commands
```bash
php artisan migrate              # Run migrations
php artisan db:seed              # Seed database
php artisan config:cache         # Cache configuration
php artisan storage:link         # Link storage to public
```

### Testing
PHPUnit configured ([phpunit.xml](phpunit.xml)). Run tests:
```bash
./vendor/bin/phpunit
```

### Installation System
This project uses `rachidlaasri/laravel-installer` for guided setup. Config in [config/installer.php](config/installer.php). Check for `storage/installed` file to detect installed state.

## Common Pitfalls

1. **Forgetting `parent_id` filtering**: Always scope queries by `parentId()` to maintain data isolation
2. **Settings caching**: Settings are fetched dynamically; changes require reloading `settings()` function
3. **Subscription limits**: Check user limits before creating new members/users/plans
4. **Membership status**: Always verify membership is 'Active' and not expired before granting access
5. **File uploads**: Files stored in `storage/upload/` - use `Storage::put()` for proper path handling

## File Locations

- Controllers: [app/Http/Controllers/](app/Http/Controllers/)
- Models: [app/Models/](app/Models/)
- Routes: [routes/web.php](routes/web.php), [routes/api.php](routes/api.php)
- Views: [resources/views/](resources/views/)
- Migrations: [database/migrations/](database/migrations/)
- Global helpers: [app/Helper/helper.php](app/Helper/helper.php)
- Config: [config/](config/)
