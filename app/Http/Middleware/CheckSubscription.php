<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Allow super admin to access everything
        if (Auth::check() && Auth::user()->type == 'super admin') {
            return $next($request);
        }

        // Check if owner has a subscription
        if (Auth::check() && Auth::user()->type == 'owner' && empty(Auth::user()->subscription)) {
            // Allow access to subscription routes, logout, and profile
            $allowedRoutes = [
                'subscriptions.index',
                'subscriptions.show',
                'subscription.stripe.payment',
                'subscription.bank.transfer',
                'subscription.paypal',
                'subscription.pay.with.paystack',
                'logout',
                'profile.edit',
                'profile.update',
            ];

            if (!in_array($request->route()->getName(), $allowedRoutes)) {
                return redirect()->route('subscriptions.index')
                    ->with('error', __('Please select a subscription plan to access this feature.'));
            }
        }

        return $next($request);
    }
}
