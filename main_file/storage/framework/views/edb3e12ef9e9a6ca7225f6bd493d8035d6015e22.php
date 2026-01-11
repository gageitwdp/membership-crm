<?php
    $admin_logo = getSettingsValByName('company_logo');
    $ids = parentId();
    $authUser = \App\Models\User::find($ids);
    $subscription = \App\Models\Subscription::find($authUser->subscription);
    $routeName = \Request::route()->getName();
    $pricing_feature_settings = getSettingsValByIdName(1, 'pricing_feature');
?>
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="#" class="b-brand text-primary">
                <img src="<?php echo e(asset(Storage::url('upload/logo/')) . '/' . (isset($admin_logo) && !empty($admin_logo) ? $admin_logo : 'logo.png')); ?>"
                    alt="" class="logo logo-lg" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label><?php echo e(__('Home')); ?></label>
                    <i class="ti ti-dashboard"></i>
                </li>
                <li class="pc-item <?php echo e(in_array($routeName, ['dashboard', 'home', '']) ? 'active' : ''); ?>">
                    <a href="<?php echo e(route('dashboard')); ?>" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
                        <span class="pc-mtext"><?php echo e(__('Dashboard')); ?></span>
                    </a>
                </li>
                <?php if(\Auth::user()->type == 'super admin'): ?>
                    <?php if(Gate::check('manage user')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['users.index', 'users.show']) ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('users.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-user-plus"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Customers')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if(Gate::check('manage user') || Gate::check('manage role') || Gate::check('manage logged history')): ?>
                        <li
                            class="pc-item pc-hasmenu <?php echo e(in_array($routeName, ['users.index', 'logged.history', 'role.index', 'role.create', 'role.edit']) ? 'pc-trigger active' : ''); ?>">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-users"></i>
                                </span>
                                <span class="pc-mtext"><?php echo e(__('Staff Management')); ?></span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: <?php echo e(in_array($routeName, ['users.index', 'logged.history', 'role.index', 'role.create', 'role.edit']) ? 'block' : 'none'); ?>">
                                <?php if(Gate::check('manage user')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['users.index']) ? 'active' : ''); ?>">
                                        <a class="pc-link" href="<?php echo e(route('users.index')); ?>"><?php echo e(__('Users')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage role')): ?>
                                    <li
                                        class="pc-item  <?php echo e(in_array($routeName, ['role.index', 'role.create', 'role.edit']) ? 'active' : ''); ?>">
                                        <a class="pc-link" href="<?php echo e(route('role.index')); ?>"><?php echo e(__('Roles')); ?> </a>
                                    </li>
                                <?php endif; ?>
                                <?php if($pricing_feature_settings == 'off' || $subscription->enabled_logged_history == 1): ?>
                                    <?php if(Gate::check('manage logged history')): ?>
                                        <li
                                            class="pc-item  <?php echo e(in_array($routeName, ['logged.history']) ? 'active' : ''); ?>">
                                            <a class="pc-link"
                                                href="<?php echo e(route('logged.history')); ?>"><?php echo e(__('Logged History')); ?></a>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if(Gate::check('manage member') ||
                        Gate::check('manage membership') ||
                        Gate::check('manage membership payment') ||
                        Gate::check('manage event') ||
                        Gate::check('manage activity tracking') ||
                        Gate::check('manage membership suspension') ||
                        Gate::check('manage expense') ||
                        Gate::check('manage contact') ||
                        Gate::check('manage note')): ?>
                    <li class="pc-item pc-caption">
                        <label><?php echo e(__('Business Management')); ?></label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>
                    <?php if(Gate::check('manage member')): ?>
                        <li
                            class="pc-item <?php echo e(in_array($routeName, ['member.index', 'member.show', 'member.edit', 'member.create']) ? 'active' : ''); ?>">
                            <a class="pc-link" href="<?php echo e(route('member.index')); ?>">
                                <span class="pc-micon"><i data-feather="user"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Member')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if(Gate::check('manage membership')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['membership.index']) ? 'active' : ''); ?>">
                            <a class="pc-link" href="<?php echo e(route('membership.index')); ?>">
                                <span class="pc-micon"><i data-feather="clipboard"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Membership')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if(Gate::check('manage membership payment')): ?>
                        <li
                            class="pc-item <?php echo e(in_array($routeName, ['membership-payment.index', 'membership-payment.show']) ? 'active' : ''); ?>">
                            <a class="pc-link" href="<?php echo e(route('membership-payment.index')); ?>">
                                <span class="pc-micon"><i data-feather="credit-card"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Payments')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if(Gate::check('manage event') || Gate::check('event calendar')): ?>
                        <li
                            class="pc-item pc-hasmenu <?php echo e(in_array($routeName, ['event.index', 'event.calendar']) ? 'pc-trigger active' : ''); ?>">

                            <a href="#!" class="pc-link">
                                <span class="pc-micon"><i data-feather="calendar"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Events')); ?></span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: <?php echo e(in_array($routeName, ['event.index', 'event.calendar']) ? 'block' : 'none'); ?>">
                                <?php if(Gate::check('manage event')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['event.index']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('event.index')); ?>"><?php echo e(__('Events List')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('event calendar')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['event.calendar']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('event.calendar')); ?>"><?php echo e(__('Calendar')); ?></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <?php if(Gate::check('manage activity tracking')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['activity-tracking.index']) ? 'active' : ''); ?>">
                            <a class="pc-link" href="<?php echo e(route('activity-tracking.index')); ?>">
                                <span class="pc-micon"><i data-feather="activity"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Activity Tracking')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if(Gate::check('manage membership suspension')): ?>
                        <li
                            class="pc-item <?php echo e(in_array($routeName, ['membership-suspension.index']) ? 'active' : ''); ?>">
                            <a class="pc-link" href="<?php echo e(route('membership-suspension.index')); ?>">
                                <span class="pc-micon"><i data-feather="alert-triangle"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Suspension')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>



                    <?php if(Gate::check('manage expense')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['expense.index']) ? 'active' : ''); ?>">
                            <a class="pc-link" href="<?php echo e(route('expense.index')); ?>">
                                <span class="pc-micon"><i data-feather="file"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Expense')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage contact')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['contact.index']) ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('contact.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-phone-call"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Contact Diary')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage note')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['note.index']) ? 'active' : ''); ?> ">
                            <a href="<?php echo e(route('note.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-notebook"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Notice Board')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage income report') || Gate::check('manage membership report')): ?>

                        <li
                            class="pc-item pc-hasmenu  <?php echo e(in_array($routeName, ['report.income', 'report.membership', 'report.expense']) ? 'pc-trigger  active' : ''); ?>">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-chart-infographic"></i>
                                </span>
                                <span class="pc-mtext"><?php echo e(__('Reports')); ?></span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: <?php echo e(in_array($routeName, ['report.income','report.membership','report.expense']) ? 'block' : 'none'); ?>">
                                <?php if(Gate::check('manage income report')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['report.income']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('report.income')); ?>"><?php echo e(__('Income')); ?></a>
                                    </li>
                                <?php endif; ?>

                                <?php if(Gate::check('manage membership report')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['report.membership']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('report.membership')); ?>"><?php echo e(__('Membership')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage expense report')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['report.expense']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('report.expense')); ?>"><?php echo e(__('Expense')); ?></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>

                    <?php endif; ?>

                <?php endif; ?>


                <?php if(Gate::check('manage document type') ||
                        Gate::check('manage expense type') ||
                        Gate::check('manage membership plan') ||
                        Gate::check('manage notification')): ?>


                    <?php if(Auth::user()->type != 'member'): ?>
                        <li class="pc-item pc-caption">
                            <label><?php echo e(__('System Configuration')); ?></label>
                            <i class="ti ti-chart-arcs"></i>
                        </li>
                    <?php endif; ?>

                    <?php if(Gate::check('manage membership plan')): ?>
                        <li
                            class="pc-item <?php echo e(in_array($routeName, ['membership-plan.index', 'membership-plan.payment']) ? 'active' : ''); ?>">
                            <a class="pc-link" href="<?php echo e(route('membership-plan.index')); ?>">
                                <span class="pc-micon"><i data-feather="layers"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Membership Plan')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if(Gate::check('manage document type')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['document-type.index']) ? 'active' : ''); ?>">
                            <a class="pc-link" href="<?php echo e(route('document-type.index')); ?>">
                                <span class="pc-micon"><i data-feather="database"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Document Type')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if(Gate::check('manage expense type')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['expense-type.index']) ? 'active' : ''); ?> ">
                            <a class="pc-link" href="<?php echo e(route('expense-type.index')); ?>">
                                <span class="pc-micon"><i data-feather="file"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Expense Type')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage notification')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['notification.index']) ? 'active' : ''); ?> ">
                            <a href="<?php echo e(route('notification.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-bell"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Email Notification')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>


                <?php if(Gate::check('manage pricing packages') ||
                        Gate::check('manage pricing transation') ||
                        Gate::check('manage account settings') ||
                        Gate::check('manage password settings') ||
                        Gate::check('manage general settings') ||
                        Gate::check('manage email settings') ||
                        Gate::check('manage payment settings') ||
                        Gate::check('manage company settings') ||
                        Gate::check('manage seo settings') ||
                        Gate::check('manage google recaptcha settings')): ?>
                    <li class="pc-item pc-caption">
                        <label><?php echo e(__('System Settings')); ?></label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>

                    <?php if(Gate::check('manage FAQ') || Gate::check('manage Page')): ?>
                        <li
                            class="pc-item pc-hasmenu <?php echo e(in_array($routeName, ['homepage.index', 'FAQ.index', 'pages.index', 'footerSetting']) ? 'active' : ''); ?>">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-layout-rows"></i>
                                </span>
                                <span class="pc-mtext"><?php echo e(__('CMS')); ?></span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: <?php echo e(in_array($routeName, ['homepage.index', 'FAQ.index', 'pages.index', 'footerSetting']) ? 'block' : 'none'); ?>">
                                <?php if(Gate::check('manage home page')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['homepage.index']) ? 'active' : ''); ?> ">
                                        <a href="<?php echo e(route('homepage.index')); ?>"
                                            class="pc-link"><?php echo e(__('Home Page')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage Page')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['pages.index']) ? 'active' : ''); ?> ">
                                        <a href="<?php echo e(route('pages.index')); ?>"
                                            class="pc-link"><?php echo e(__('Custom Page')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage FAQ')): ?>
                                    <li class="pc-item <?php echo e(in_array($routeName, ['FAQ.index']) ? 'active' : ''); ?> ">
                                        <a href="<?php echo e(route('FAQ.index')); ?>" class="pc-link"><?php echo e(__('FAQ')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage footer')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['footerSetting']) ? 'active' : ''); ?> ">
                                        <a href="<?php echo e(route('footerSetting')); ?>"
                                            class="pc-link"><?php echo e(__('Footer')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage auth page')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['authPage.index']) ? 'active' : ''); ?> ">
                                        <a href="<?php echo e(route('authPage.index')); ?>"
                                            class="pc-link"><?php echo e(__('Auth Page')); ?></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <?php if(Auth::user()->type == 'super admin' || $pricing_feature_settings == 'on'): ?>
                        <?php if(Gate::check('manage pricing packages') || Gate::check('manage pricing transation')): ?>
                            <li
                                class="pc-item pc-hasmenu <?php echo e(in_array($routeName, ['subscriptions.index', 'subscriptions.show', 'subscription.transaction']) ? 'pc-trigger active' : ''); ?>">
                                <a href="#!" class="pc-link">
                                    <span class="pc-micon">
                                        <i class="ti ti-package"></i>
                                    </span>
                                    <span class="pc-mtext"><?php echo e(__('Pricing')); ?></span>
                                    <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                                </a>
                                <ul class="pc-submenu"
                                    style="display: <?php echo e(in_array($routeName, ['subscriptions.index', 'subscriptions.show', 'subscription.transaction']) ? 'block' : 'none'); ?>">
                                    <?php if(Gate::check('manage pricing packages')): ?>
                                        <li
                                            class="pc-item <?php echo e(in_array($routeName, ['subscriptions.index', 'subscriptions.show']) ? 'active' : ''); ?>">
                                            <a class="pc-link"
                                                href="<?php echo e(route('subscriptions.index')); ?>"><?php echo e(__('Packages')); ?></a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if(Gate::check('manage pricing transation')): ?>
                                        <li
                                            class="pc-item <?php echo e(in_array($routeName, ['subscription.transaction']) ? 'active' : ''); ?>">
                                            <a class="pc-link"
                                                href="<?php echo e(route('subscription.transaction')); ?>"><?php echo e(__('Transactions')); ?></a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if(Gate::check('manage coupon') || Gate::check('manage coupon history')): ?>
                        <li
                            class="pc-item pc-hasmenu <?php echo e(in_array($routeName, ['coupons.index', 'coupons.history']) ? 'active' : ''); ?>">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-shopping-cart-discount"></i>
                                </span>
                                <span class="pc-mtext"><?php echo e(__('Coupons')); ?></span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: <?php echo e(in_array($routeName, ['coupons.index', 'coupons.history']) ? 'block' : 'none'); ?>">
                                <?php if(Gate::check('manage coupon')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['coupons.index']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('coupons.index')); ?>"><?php echo e(__('All Coupon')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if(Gate::check('manage coupon history')): ?>
                                    <li
                                        class="pc-item <?php echo e(in_array($routeName, ['coupons.history']) ? 'active' : ''); ?>">
                                        <a class="pc-link"
                                            href="<?php echo e(route('coupons.history')); ?>"><?php echo e(__('Coupon History')); ?></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage account settings') ||
                            Gate::check('manage password settings') ||
                            Gate::check('manage general settings') ||
                            Gate::check('manage email settings') ||
                            Gate::check('manage payment settings') ||
                            Gate::check('manage company settings') ||
                            Gate::check('manage seo settings') ||
                            Gate::check('manage google recaptcha settings')): ?>
                        <li class="pc-item <?php echo e(in_array($routeName, ['setting.index']) ? 'active' : ''); ?> ">
                            <a href="<?php echo e(route('setting.index')); ?>" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-settings"></i></span>
                                <span class="pc-mtext"><?php echo e(__('Settings')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                <?php endif; ?>
            </ul>
            <div class="w-100 text-center">
                <div class="badge theme-version badge rounded-pill bg-light text-dark f-12"></div>
            </div>
        </div>
    </div>
</nav>
<?php /**PATH /home/u417948420/domains/members-triumphwest.triumphtrained.com/public_html/main_file/resources/views/admin/menu.blade.php ENDPATH**/ ?>