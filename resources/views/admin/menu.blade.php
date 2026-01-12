@php
    $admin_logo = getSettingsValByName('company_logo');
    $ids = Auth::check() ? parentId() : 1; // guest-safe tenant context
    $authUser = \App\Models\User::find($ids);
    $subscription = \App\Models\Subscription::find(optional($authUser)->subscription);
    $routeName = \Request::route()->getName();
    $pricing_feature_settings = getSettingsValByIdName(1, 'pricing_feature');
    $userType = optional(Auth::user())->type; // guest-safe user type
@endphp

{{-- Top-level public links (safe for guests) --}}
- {{ __('Home') }}
- [{{ __('Dashboard') }} ]({{ route('dashboard') }})

{{-- SUPER ADMIN & STAFF MANAGEMENT --}}
@auth
    @if ($userType === 'super admin')
        @if (Gate::check('manage user'))
            - [{{ __('Customers') }} ]({{ route('users.index') }})
        @endif
    @else
        @if (Gate::check('manage user') || Gate::check('manage role') || Gate::check('manage logged history'))
            - {{ __('Staff Management') }}<br>
            @if (Gate::check('manage user'))
                - [{{ __('Users') }}]({{ route('users.index') }})
            @endif
            @if (Gate::check('manage role'))
                - [{{ __('Roles') }} ]({{ route('role.index') }})
            @endif
            @if ($pricing_feature_settings == 'off' && optional($subscription)->enabled_logged_history == 1)
                @if (Gate::check('manage logged history'))
                    - [{{ __('Logged History') }}]({{ route('logged.history') }})
                @endif
            @endif
        @endif
    @endif
@endauth

{{-- BUSINESS MANAGEMENT --}}
@auth
    @if (Gate::check('manage member') || Gate::check('manage membership') || Gate::check('manage membership payment') ||
         Gate::check('manage event') || Gate::check('manage activity tracking') || Gate::check('manage membership suspension') ||
         Gate::check('manage expense') || Gate::check('manage contact') || Gate::check('manage note'))
        - {{ __('Business Management') }}
        @if (Gate::check('manage member'))
            - [{{ __('Member') }} ]({{ route('member.index') }})
        @endif
        @if (Gate::check('manage membership'))
            - [{{ __('Membership') }} ]({{ route('membership.index') }})
        @endif
        @if (Gate::check('manage membership payment'))
            - [{{ __('Payments') }} ]({{ route('membership-payment.index') }})
        @endif
        @if (Gate::check('manage event') || Gate::check('event calendar'))
            - {{ __('Events') }}<br>
            @if (Gate::check('manage event'))
                - [{{ __('Events List') }}]({{ route('event.index') }})
            @endif
            @if (Gate::check('event calendar'))
                - [{{ __('Calendar') }}]({{ route('event.calendar') }})
            @endif
        @endif
        @if (Gate::check('manage activity tracking'))
            - [{{ __('Activity Tracking') }} ]({{ route('activity-tracking.index') }})
        @endif
        @if (Gate::check('manage membership suspension'))
            - [{{ __('Suspension') }} ]({{ route('membership-suspension.index') }})
        @endif
        @if (Gate::check('manage expense'))
            - [{{ __('Expense') }} ]({{ route('expense.index') }})
        @endif
        @if (Gate::check('manage contact'))
            - [{{ __('Contact Diary') }} ]({{ route('contact.index') }})
        @endif
        @if (Gate::check('manage note'))
            - [{{ __('Notice Board') }} ]({{ route('note.index') }})
        @endif
        @if (Gate::check('manage income report') || Gate::check('manage membership report') || Gate::check('manage expense report'))
            - {{ __('Reports') }}<br>
            @if (Gate::check('manage income report'))
                - [{{ __('Income') }}]({{ route('report.income') }})
            @endif
            @if (Gate::check('manage membership report'))
                - [{{ __('Membership') }}]({{ route('report.membership') }})
            @endif
            @if (Gate::check('manage expense report'))
                - [{{ __('Expense') }}]({{ route('report.expense') }})
            @endif
        @endif
    @endif
@endauth

{{-- SYSTEM CONFIGURATION --}}
@auth
    @if (Gate::check('manage document type') || Gate::check('manage expense type') || Gate::check('manage membership plan') || Gate::check('manage notification'))
        @if ($userType !== 'member')
            - {{ __('System Configuration') }}
        @endif
        @if (Gate::check('manage membership plan'))
            - [{{ __('Membership Plan') }} ]({{ route('membership-plan.index') }})
        @endif
        @if (Gate::check('manage document type'))
            - [{{ __('Document Type') }} ]({{ route('document-type.index') }})
        @endif
        @if (Gate::check('manage expense type'))
            - [{{ __('Expense Type') }} ]({{ route('expense-type.index') }})
        @endif
        @if (Gate::check('manage notification'))
            - [{{ __('Email Notification') }} ]({{ route('notification.index') }})
        @endif
    @endif
@endauth

{{-- SYSTEM SETTINGS / CMS --}}
@auth
    @if (Gate::check('manage pricing packages') || Gate::check('manage pricing transation') ||
         Gate::check('manage account settings') || Gate::check('manage password settings') || Gate::check('manage general settings') ||
         Gate::check('manage email settings') || Gate::check('manage payment settings') || Gate::check('manage company settings') ||
         Gate::check('manage seo settings') || Gate::check('manage google recaptcha settings'))
        - {{ __('System Settings') }}
        @if (Gate::check('manage FAQ') || Gate::check('manage Page') || Gate::check('manage home page') || Gate::check('manage footer') || Gate::check('manage auth page'))
            - {{ __('CMS') }}<br>
            @if (Gate::check('manage home page'))
                - [{{ __('Home Page') }}]({{ route('homepage.index') }})
            @endif
            @if (Gate::check('manage Page'))
                - [{{ __('Custom Page') }}]({{ route('pages.index') }})
            @endif
            @if (Gate::check('manage FAQ'))
                - [{{ __('FAQ') }}]({{ route('FAQ.index') }})
            @endif
            @if (Gate::check('manage footer'))
                - [{{ __('Footer') }}]({{ route('footerSetting') }})
            @endif
            @if (Gate::check('manage auth page'))
                - [{{ __('Auth Page') }}]({{ route('authPage.index') }})
            @endif
        @endif
    @endif
@endauth

{{-- PRICING (super admin only when pricing feature is on) --}}
@auth
    @if ($userType === 'super admin' && $pricing_feature_settings == 'on')
        @if (Gate::check('manage pricing packages') || Gate::check('manage pricing transation'))
            - {{ __('Pricing') }}<br>
            @if (Gate::check('manage pricing packages'))
                - [{{ __('Packages') }}]({{ route('subscriptions.index') }})
            @endif
            @if (Gate::check('manage pricing transation'))
                - [{{ __('Transactions') }}]({{ route('subscription.transaction') }})
            @endif
        @endif
    @endif
@endauth

{{-- COUPONS --}}
@auth
    @if (Gate::check('manage coupon') || Gate::check('manage coupon history'))
        - {{ __('Coupons') }}<br>
        @if (Gate::check('manage coupon'))
            - [{{ __('All Coupon') }}]({{ route('coupons.index') }})
        @endif
        @if (Gate::check('manage coupon history'))
            - [{{ __('Coupon History') }}]({{ route('coupons.history') }})
        @endif
    @endif
@endauth

{{-- SETTINGS (shortcut) --}}
@auth
    @if (Gate::check('manage account settings') || Gate::check('manage password settings') || Gate::check('manage general settings') ||
         Gate::check('manage email settings') || Gate::check('manage payment settings') || Gate::check('manage company settings') ||
         Gate::check('manage seo settings') || Gate::check('manage google recaptcha settings'))
        - [{{ __('Settings') }} ]({{ route('setting.index') }})
    @endif
@endauth
