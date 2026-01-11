@php
    $admin_logo = getSettingsValByName('company_logo');
    $ids = parentId();
    $authUser = \App\Models\User::find($ids);
    $subscription = \App\Models\Subscription::find($authUser->subscription);
    $routeName = \Request::route()->getName();
    $pricing_feature_settings = getSettingsValByIdName(1, 'pricing_feature');
@endphp
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="#" class="b-brand text-primary">
                <img src="{{ asset(Storage::url('upload/logo/')) . '/' . (isset($admin_logo) && !empty($admin_logo) ? $admin_logo : 'logo.png') }}"
                    alt="" class="logo logo-lg" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label>{{ __('Home') }}</label>
                    <i class="ti ti-dashboard"></i>
                </li>
                <li class="pc-item {{ in_array($routeName, ['dashboard', 'home', '']) ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
                        <span class="pc-mtext">{{ __('Dashboard') }}</span>
                    </a>
                </li>
                @if (\Auth::user()->type == 'super admin')
                    @if (Gate::check('manage user'))
                        <li class="pc-item {{ in_array($routeName, ['users.index', 'users.show']) ? 'active' : '' }}">
                            <a href="{{ route('users.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-user-plus"></i></span>
                                <span class="pc-mtext">{{ __('Customers') }}</span>
                            </a>
                        </li>
                    @endif
                @else
                    @if (Gate::check('manage user') || Gate::check('manage role') || Gate::check('manage logged history'))
                        <li
                            class="pc-item pc-hasmenu {{ in_array($routeName, ['users.index', 'logged.history', 'role.index', 'role.create', 'role.edit']) ? 'pc-trigger active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-users"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Staff Management') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['users.index', 'logged.history', 'role.index', 'role.create', 'role.edit']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage user'))
                                    <li class="pc-item {{ in_array($routeName, ['users.index']) ? 'active' : '' }}">
                                        <a class="pc-link" href="{{ route('users.index') }}">{{ __('Users') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage role'))
                                    <li
                                        class="pc-item  {{ in_array($routeName, ['role.index', 'role.create', 'role.edit']) ? 'active' : '' }}">
                                        <a class="pc-link" href="{{ route('role.index') }}">{{ __('Roles') }} </a>
                                    </li>
                                @endif
                                @if ($pricing_feature_settings == 'off' || $subscription->enabled_logged_history == 1)
                                    @if (Gate::check('manage logged history'))
                                        <li
                                            class="pc-item  {{ in_array($routeName, ['logged.history']) ? 'active' : '' }}">
                                            <a class="pc-link"
                                                href="{{ route('logged.history') }}">{{ __('Logged History') }}</a>
                                        </li>
                                    @endif
                                @endif
                            </ul>
                        </li>
                    @endif
                @endif
                @if (Gate::check('manage member') ||
                        Gate::check('manage membership') ||
                        Gate::check('manage membership payment') ||
                        Gate::check('manage event') ||
                        Gate::check('manage activity tracking') ||
                        Gate::check('manage membership suspension') ||
                        Gate::check('manage expense') ||
                        Gate::check('manage contact') ||
                        Gate::check('manage note'))
                    <li class="pc-item pc-caption">
                        <label>{{ __('Business Management') }}</label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>
                    @if (Gate::check('manage member'))
                        <li
                            class="pc-item {{ in_array($routeName, ['member.index', 'member.show', 'member.edit', 'member.create']) ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('member.index') }}">
                                <span class="pc-micon"><i data-feather="user"></i></span>
                                <span class="pc-mtext">{{ __('Member') }}</span>
                            </a>
                        </li>
                    @endif

                    @if (Gate::check('manage membership'))
                        <li class="pc-item {{ in_array($routeName, ['membership.index']) ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('membership.index') }}">
                                <span class="pc-micon"><i data-feather="clipboard"></i></span>
                                <span class="pc-mtext">{{ __('Membership') }}</span>
                            </a>
                        </li>
                    @endif

                    @if (Gate::check('manage membership payment'))
                        <li
                            class="pc-item {{ in_array($routeName, ['membership-payment.index', 'membership-payment.show']) ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('membership-payment.index') }}">
                                <span class="pc-micon"><i data-feather="credit-card"></i></span>
                                <span class="pc-mtext">{{ __('Payments') }}</span>
                            </a>
                        </li>
                    @endif

                    @if (Gate::check('manage event') || Gate::check('event calendar'))
                        <li
                            class="pc-item pc-hasmenu {{ in_array($routeName, ['event.index', 'event.calendar']) ? 'pc-trigger active' : '' }}">

                            <a href="#!" class="pc-link">
                                <span class="pc-micon"><i data-feather="calendar"></i></span>
                                <span class="pc-mtext">{{ __('Events') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['event.index', 'event.calendar']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage event'))
                                    <li class="pc-item {{ in_array($routeName, ['event.index']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('event.index') }}">{{ __('Events List') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('event calendar'))
                                    <li class="pc-item {{ in_array($routeName, ['event.calendar']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('event.calendar') }}">{{ __('Calendar') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if (Gate::check('manage activity tracking'))
                        <li class="pc-item {{ in_array($routeName, ['activity-tracking.index']) ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('activity-tracking.index') }}">
                                <span class="pc-micon"><i data-feather="activity"></i></span>
                                <span class="pc-mtext">{{ __('Activity Tracking') }}</span>
                            </a>
                        </li>
                    @endif

                    @if (Gate::check('manage membership suspension'))
                        <li
                            class="pc-item {{ in_array($routeName, ['membership-suspension.index']) ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('membership-suspension.index') }}">
                                <span class="pc-micon"><i data-feather="alert-triangle"></i></span>
                                <span class="pc-mtext">{{ __('Suspension') }}</span>
                            </a>
                        </li>
                    @endif



                    @if (Gate::check('manage expense'))
                        <li class="pc-item {{ in_array($routeName, ['expense.index']) ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('expense.index') }}">
                                <span class="pc-micon"><i data-feather="file"></i></span>
                                <span class="pc-mtext">{{ __('Expense') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage contact'))
                        <li class="pc-item {{ in_array($routeName, ['contact.index']) ? 'active' : '' }}">
                            <a href="{{ route('contact.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-phone-call"></i></span>
                                <span class="pc-mtext">{{ __('Contact Diary') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage note'))
                        <li class="pc-item {{ in_array($routeName, ['note.index']) ? 'active' : '' }} ">
                            <a href="{{ route('note.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-notebook"></i></span>
                                <span class="pc-mtext">{{ __('Notice Board') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage income report') || Gate::check('manage membership report'))

                        <li
                            class="pc-item pc-hasmenu  {{ in_array($routeName, ['report.income', 'report.membership', 'report.expense']) ? 'pc-trigger  active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-chart-infographic"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Reports') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['report.income','report.membership','report.expense']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage income report'))
                                    <li class="pc-item {{ in_array($routeName, ['report.income']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('report.income') }}">{{ __('Income') }}</a>
                                    </li>
                                @endif

                                @if (Gate::check('manage membership report'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['report.membership']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('report.membership') }}">{{ __('Membership') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage expense report'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['report.expense']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('report.expense') }}">{{ __('Expense') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>

                    @endif

                @endif


                @if (Gate::check('manage document type') ||
                        Gate::check('manage expense type') ||
                        Gate::check('manage membership plan') ||
                        Gate::check('manage notification'))


                    @if (Auth::user()->type != 'member')
                        <li class="pc-item pc-caption">
                            <label>{{ __('System Configuration') }}</label>
                            <i class="ti ti-chart-arcs"></i>
                        </li>
                    @endif

                    @if (Gate::check('manage membership plan'))
                        <li
                            class="pc-item {{ in_array($routeName, ['membership-plan.index', 'membership-plan.payment']) ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('membership-plan.index') }}">
                                <span class="pc-micon"><i data-feather="layers"></i></span>
                                <span class="pc-mtext">{{ __('Membership Plan') }}</span>
                            </a>
                        </li>
                    @endif

                    @if (Gate::check('manage document type'))
                        <li class="pc-item {{ in_array($routeName, ['document-type.index']) ? 'active' : '' }}">
                            <a class="pc-link" href="{{ route('document-type.index') }}">
                                <span class="pc-micon"><i data-feather="database"></i></span>
                                <span class="pc-mtext">{{ __('Document Type') }}</span>
                            </a>
                        </li>
                    @endif

                    @if (Gate::check('manage expense type'))
                        <li class="pc-item {{ in_array($routeName, ['expense-type.index']) ? 'active' : '' }} ">
                            <a class="pc-link" href="{{ route('expense-type.index') }}">
                                <span class="pc-micon"><i data-feather="file"></i></span>
                                <span class="pc-mtext">{{ __('Expense Type') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage notification'))
                        <li class="pc-item {{ in_array($routeName, ['notification.index']) ? 'active' : '' }} ">
                            <a href="{{ route('notification.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-bell"></i></span>
                                <span class="pc-mtext">{{ __('Email Notification') }}</span>
                            </a>
                        </li>
                    @endif
                @endif


                @if (Gate::check('manage pricing packages') ||
                        Gate::check('manage pricing transation') ||
                        Gate::check('manage account settings') ||
                        Gate::check('manage password settings') ||
                        Gate::check('manage general settings') ||
                        Gate::check('manage email settings') ||
                        Gate::check('manage payment settings') ||
                        Gate::check('manage company settings') ||
                        Gate::check('manage seo settings') ||
                        Gate::check('manage google recaptcha settings'))
                    <li class="pc-item pc-caption">
                        <label>{{ __('System Settings') }}</label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>

                    @if (Gate::check('manage FAQ') || Gate::check('manage Page'))
                        <li
                            class="pc-item pc-hasmenu {{ in_array($routeName, ['homepage.index', 'FAQ.index', 'pages.index', 'footerSetting']) ? 'active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-layout-rows"></i>
                                </span>
                                <span class="pc-mtext">{{ __('CMS') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['homepage.index', 'FAQ.index', 'pages.index', 'footerSetting']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage home page'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['homepage.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('homepage.index') }}"
                                            class="pc-link">{{ __('Home Page') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage Page'))
                                    <li class="pc-item {{ in_array($routeName, ['pages.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('pages.index') }}"
                                            class="pc-link">{{ __('Custom Page') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage FAQ'))
                                    <li class="pc-item {{ in_array($routeName, ['FAQ.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('FAQ.index') }}" class="pc-link">{{ __('FAQ') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage footer'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['footerSetting']) ? 'active' : '' }} ">
                                        <a href="{{ route('footerSetting') }}"
                                            class="pc-link">{{ __('Footer') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage auth page'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['authPage.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('authPage.index') }}"
                                            class="pc-link">{{ __('Auth Page') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                    @if (Auth::user()->type == 'super admin' || $pricing_feature_settings == 'on')
                        @if (Gate::check('manage pricing packages') || Gate::check('manage pricing transation'))
                            <li
                                class="pc-item pc-hasmenu {{ in_array($routeName, ['subscriptions.index', 'subscriptions.show', 'subscription.transaction']) ? 'pc-trigger active' : '' }}">
                                <a href="#!" class="pc-link">
                                    <span class="pc-micon">
                                        <i class="ti ti-package"></i>
                                    </span>
                                    <span class="pc-mtext">{{ __('Pricing') }}</span>
                                    <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                                </a>
                                <ul class="pc-submenu"
                                    style="display: {{ in_array($routeName, ['subscriptions.index', 'subscriptions.show', 'subscription.transaction']) ? 'block' : 'none' }}">
                                    @if (Gate::check('manage pricing packages'))
                                        <li
                                            class="pc-item {{ in_array($routeName, ['subscriptions.index', 'subscriptions.show']) ? 'active' : '' }}">
                                            <a class="pc-link"
                                                href="{{ route('subscriptions.index') }}">{{ __('Packages') }}</a>
                                        </li>
                                    @endif
                                    @if (Gate::check('manage pricing transation'))
                                        <li
                                            class="pc-item {{ in_array($routeName, ['subscription.transaction']) ? 'active' : '' }}">
                                            <a class="pc-link"
                                                href="{{ route('subscription.transaction') }}">{{ __('Transactions') }}</a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                    @endif
                    @if (Gate::check('manage coupon') || Gate::check('manage coupon history'))
                        <li
                            class="pc-item pc-hasmenu {{ in_array($routeName, ['coupons.index', 'coupons.history']) ? 'active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-shopping-cart-discount"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Coupons') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['coupons.index', 'coupons.history']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage coupon'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['coupons.index']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('coupons.index') }}">{{ __('All Coupon') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage coupon history'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['coupons.history']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('coupons.history') }}">{{ __('Coupon History') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                    @if (Gate::check('manage account settings') ||
                            Gate::check('manage password settings') ||
                            Gate::check('manage general settings') ||
                            Gate::check('manage email settings') ||
                            Gate::check('manage payment settings') ||
                            Gate::check('manage company settings') ||
                            Gate::check('manage seo settings') ||
                            Gate::check('manage google recaptcha settings'))
                        <li class="pc-item {{ in_array($routeName, ['setting.index']) ? 'active' : '' }} ">
                            <a href="{{ route('setting.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-settings"></i></span>
                                <span class="pc-mtext">{{ __('Settings') }}</span>
                            </a>
                        </li>
                    @endif

                @endif
            </ul>
            <div class="w-100 text-center">
                <div class="badge theme-version badge rounded-pill bg-light text-dark f-12"></div>
            </div>
        </div>
    </div>
</nav>
