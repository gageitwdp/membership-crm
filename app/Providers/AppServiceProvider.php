<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// ✅ Use the correct facades:
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
        $lang = optional(Auth::user())->lang
            ?? Cookie::get('lang')
            ?? config('app.locale')   // fallback to app locale
            ?? 'en';

        app()->setLocale($lang);

        // Share with every Blade view as $lang
        View::share('lang', $lang);


        $languages = config('app.available_locales')
            ?? ['en' => 'English', 'es' => 'Español', 'fr' => 'Français', 'ar' => 'العربية'];
        View::share('languages', $languages);

    }
}
