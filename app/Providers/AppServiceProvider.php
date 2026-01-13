<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Decide language once, safely for guests and users
        // Order: user preference → session → cookie → app locale → 'en'
        $lang = optional(Auth::user())->lang
            ?? session('lang')
            ?? Cookie::get('lang')
            ?? config('app.locale')
            ?? 'en';

        // (Optional) Whitelist to supported locales to avoid typos:
        $supported = config('app.supported_locales')        // e.g. ['en','es','fr','ar']
            ?? array_keys(config('app.available_locales', ['en' => 'English']));
        if (!empty($supported) && !in_array($lang, $supported, true)) {
            $lang = config('app.locale', 'en');
        }

        app()->setLocale($lang);

        // Share with every Blade view
        View::share('lang', $lang);
        View::share('userLang', $lang); // ← alias for legacy views

        // Expose the map of languages (falls back if config missing)
        $languages = config('app.available_locales')
            ?? ['en' => 'English', 'es' => 'Español', 'fr' => 'Français', 'ar' => 'العربية'];

        View::share('languages', $languages);

	$profile = auth()->user() ?? null;
	View::share('profile', $profile);

    }
}

