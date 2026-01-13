<?php
use App\Mail\Common;
use App\Mail\EmailVerification;
use App\Mail\TestMail;
use App\Models\ActivityTracking;
use App\Models\AuthPage;
use App\Models\Custom;
use App\Models\Event;
use App\Models\FAQ;
use App\Models\HomePage;
use App\Models\LoggedHistory;
use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipPayment;
use App\Models\MembershipSuspension;
use App\Models\Notification;
use App\Models\Page;
use App\Models\Subscription;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PragmaRX\Google2FAQRCode\Google2FA;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

if (!function_exists('settingsKeys')) {
    function settingsKeys()
    {
        return $settingsKeys = [
            "app_name" => "",
            "theme_mode" => "light",
            "layout_font" => "Roboto",
            "accent_color" => "preset-6",
            "sidebar_caption" => "true",
            "theme_layout" => "ltr",
            "layout_width" => "false",
            "owner_email_verification" => "off",
            "landing_page" => "on",
            "register_page" => "on",
            "company_logo" => "logo.png",
            "company_favicon" => "favicon.png",
            "landing_logo" => "landing_logo.png",
            "light_logo" => "light_logo.png",
            "meta_seo_title" => "",
            "meta_seo_keyword" => "",
            "meta_seo_description" => "",
            "meta_seo_image" => "",
            "company_date_format" => "M j, Y",
            "company_time_format" => "g:i A",
            "company_name" => "",
            "company_phone" => "",
            "company_address" => "",
            "company_email" => "",
            "company_email_from_name" => "",
            "google_recaptcha" => "off",
            "recaptcha_key" => "",
            "recaptcha_secret" => "",
            'SERVER_DRIVER' => "",
            'SERVER_HOST' => "",
            'SERVER_PORT' => "",
            'SERVER_USERNAME' => "",
            'SERVER_PASSWORD' => "",
            'SERVER_ENCRYPTION' => "",
            'FROM_EMAIL' => "",
            'FROM_NAME' => "",
            "plan_number_prefix" => "#PLA-000",
            "event_number_prefix" => "#EVE-000",
            "suspension_number_prefix" => "#SUS-000",
            "expense_number_prefix" => "#EXP-000",
            "payment_number_prefix" => "#PAY-000",
            "invoice_number_prefix" => "#INV-000",
            "member_prefix" => "#MBR-000",
            'CURRENCY' => "USD",
            'CURRENCY_SYMBOL' => "$",
            'STRIPE_PAYMENT' => "off",
            'STRIPE_KEY' => "",
            'STRIPE_SECRET' => "",
            "paypal_payment" => "off",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "bank_transfer_payment" => "off",
            "bank_name" => "",
            "bank_holder_name" => "",
            "bank_account_number" => "",
            "bank_ifsc_code" => "",
            "bank_other_details" => "",
            "flutterwave_payment" => "off",
            "flutterwave_public_key" => "",
            "flutterwave_secret_key" => "",
            "timezone" => "",
            "footer_column_1" => "Quick Links",
            "footer_column_1_enabled" => "active",
            "footer_column_2" => "Help",
            "footer_column_2_enabled" => "active",
            "footer_column_3" => "OverView",
            "footer_column_3_enabled" => "active",
            "footer_column_4" => "Core System",
            "footer_column_4_enabled" => "active",
            "pricing_feature" => "on",
            "color_type" => "preset",
            "custom_color" => "--primary-rgb: 0,0,0",
            "custom_color_code" => "#000000",
            "paystack_payment" => "off",
            "paystack_public_key" => "",
            "paystack_secret_key" => "",
            'twilio_sid' => '',
            'twilio_token' => '',
            'twilio_from_number' => '',
        ];
    }
}

if (!function_exists('settings')) {
    function settings()
    {
        $settingData = DB::table('settings');
        if (Auth::check()) {
            $userId = parentId();
            $settingData = $settingData->where('parent_id', $userId);
        } else {
            // Guest defaults
            $settingData = $settingData->where('parent_id', 1);
        }
        $settingData = $settingData->get();
        $details = settingsKeys();
        foreach ($settingData as $row) {
            $details[$row->name] = $row->value;
        }
        config([
            'captcha.secret' => $details['recaptcha_secret'],
            'captcha.sitekey' => $details['recaptcha_key'],
            'options' => [
                'timeout' => 30,
            ],
        ]);
        return $details;
    }
}

if (!function_exists('subscriptionPaymentSettings')) {
    function subscriptionPaymentSettings()
    {
        $settingData = DB::table('settings')->where('type', 'payment')->where('parent_id', '=', 1)->get();
        $result = [
            'CURRENCY' => "USD",
            'CURRENCY_SYMBOL' => "$",
            'STRIPE_PAYMENT' => "off",
            'STRIPE_KEY' => "",
            'STRIPE_SECRET' => "",
            "paypal_payment" => "off",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "bank_transfer_payment" => "off",
            "bank_name" => "",
            "bank_holder_name" => "",
            "bank_account_number" => "",
            "bank_ifsc_code" => "",
            "bank_other_details" => "",
            "flutterwave_payment" => "off",
            "flutterwave_public_key" => "",
            "flutterwave_secret_key" => "",
            "paystack_payment" => "off",
            "paystack_public_key" => "",
            "paystack_secret_key" => "",
        ];
        foreach ($settingData as $setting) {
            $result[$setting->name] = $setting->value;
        }
        return $result;
    }
}

if (!function_exists('invoicePaymentSettings')) {
    function invoicePaymentSettings($id)
    {
        $settingData = DB::table('settings')->where('type', 'payment')->where('parent_id', $id)->get();
        $result = [
            'CURRENCY' => "USD",
            'CURRENCY_SYMBOL' => "$",
            'STRIPE_PAYMENT' => "off",
            'STRIPE_KEY' => "",
            'STRIPE_SECRET' => "",
            "paypal_payment" => "off",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "bank_transfer_payment" => "off",
            "bank_name" => "",
            "bank_holder_name" => "",
            "bank_account_number" => "",
            "bank_ifsc_code" => "",
            "bank_other_details" => "",
            "flutterwave_payment" => "off",
            "flutterwave_public_key" => "",
            "flutterwave_secret_key" => "",
            "paystack_payment" => "off",
            "paystack_public_key" => "",
            "paystack_secret_key" => "",
        ];
        foreach ($settingData as $row) {
            $result[$row->name] = $row->value;
        }
        return $result;
    }
}

if (!function_exists('getSettingsValByName')) {
    function getSettingsValByName($key)
    {
        $setting = settings();
        if (!isset($setting[$key]) || empty($setting[$key])) {
            $setting[$key] = '';
        }
        return $setting[$key];
    }
}

if (!function_exists('getSettingsValByIdName')) {
    function getSettingsValByIdName($id, $key)
    {
        $setting = settingsById($id);
        if (!isset($setting[$key]) || empty($setting[$key])) {
            $setting[$key] = '';
        }
        return $setting[$key];
    }
}

if (!function_exists('settingDateFormat')) {
    function settingDateFormat($settings, $date)
    {
        return date($settings['company_date_format'], strtotime($date));
    }
}

if (!function_exists('settingPriceFormat')) {
    function settingPriceFormat($settings, $price)
    {
        return $settings['CURRENCY_SYMBOL'] . $price;
    }
}

if (!function_exists('settingTimeFormat')) {
    function settingTimeFormat($settings, $time)
    {
        return date($settings['company_time_format'], strtotime($time));
    }
}

if (!function_exists('dateFormat')) {
    function dateFormat($date)
    {
        $settings = settings();
        return date($settings['company_date_format'], strtotime($date));
    }
}

if (!function_exists('timeFormat')) {
    function timeFormat($time)
    {
        $settings = settings();
        return date($settings['company_time_format'], strtotime($time));
    }
}

if (!function_exists('priceFormat')) {
    function priceFormat($price)
    {
        $settings = settings();
        return $settings['CURRENCY_SYMBOL'] . $price;
    }
}

if (!function_exists('parentId')) {
    function parentId()
    {
        // Guests: use the same public tenant as settings() for consistency
        if (!Auth::check()) {
            return 1; // or config('app.public_tenant_id') if you set it
        }

        $user = Auth::user();
        $type = $user->type ?? null;

        if ($type === 'owner' || $type === 'super admin') {
            return $user->id;
        }
        return $user->parent_id ?? $user->id;
    }
}

if (!function_exists('lastMembershipPlan')) {
    function lastMembershipPlan()
    {
        $Member = Member::where('user_id', Auth::user()->id)->first();
        return Membership::where('member_id', $Member->id ?? 0)->orderBy('id', 'desc')->first();
    }
}

if (!function_exists('assignSubscription')) {
    function assignSubscription($id)
    {
        $subscription = Subscription::find($id);
        if ($subscription) {
            Auth::user()->subscription = $subscription->id;
            if ($subscription->interval == 'Monthly') {
                Auth::user()->subscription_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            } elseif ($subscription->interval == 'Quarterly') {
                Auth::user()->subscription_expire_date = Carbon::now()->addMonths(3)->isoFormat('YYYY-MM-DD');
            } elseif ($subscription->interval == 'Yearly') {
                Auth::user()->subscription_expire_date = Carbon::now()->addYears(1)->isoFormat('YYYY-MM-DD');
            } else {
                Auth::user()->subscription_expire_date = null;
            }
            Auth::user()->save();
            $users = User::where('parent_id', '=', parentId())->whereNotIn('type', ['super admin', 'owner'])->get();
            if ($subscription->user_limit == 0) {
                foreach ($users as $user) {
                    $user->is_active = 1;
                    $user->save();
                }
            } else {
                $userCount = 0;
                foreach ($users as $user) {
                    $userCount++;
                    if ($userCount <= $subscription->user_limit) {
                        $user->is_active = 1;
                        $user->save();
                    } else {
                        $user->is_active = 0;
                        $user->save();
                    }
                }
            }
            return [
                'is_success' => true,
            ];
        } else {
            return [
                'is_success' => false,
                'error' => 'Subscription is deleted.',
            ];
        }
    }
}

if (!function_exists('assignManuallySubscription')) {
    function assignManuallySubscription($id, $userId)
    {
        $owner = User::find($userId);
        $subscription = Subscription::find($id);
        if ($subscription) {
            $owner->subscription = $subscription->id;
            if ($subscription->interval == 'Monthly') {
                $owner->subscription_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            } elseif ($subscription->interval == 'Quarterly') {
                $owner->subscription_expire_date = Carbon::now()->addMonths(3)->isoFormat('YYYY-MM-DD');
            } elseif ($subscription->interval == 'Yearly') {
                $owner->subscription_expire_date = Carbon::now()->addYears(1)->isoFormat('YYYY-MM-DD');
            } else {
                $owner->subscription_expire_date = null;
            }
            $owner->save();
            $users = User::where('parent_id', '=', parentId())->whereNotIn('type', ['super admin', 'owner'])->get();
            if ($subscription->user_limit == 0) {
                foreach ($users as $user) {
                    $user->is_active = 1;
                    $user->save();
                }
            } else {
                $userCount = 0;
                foreach ($users as $user) {
                    $userCount++;
                    if ($userCount <= $subscription->user_limit) {
                        $user->is_active = 1;
                        $user->save();
                    } else {
                        $user->is_active = 0;
                        $user->save();
                    }
                }
            }
            return [
                'is_success' => true,
            ];
        } else {
            return [
                'is_success' => false,
                'error' => 'Subscription is deleted.',
            ];
        }
    }
}

if (!function_exists('smtpDetail')) {
    function smtpDetail($id)
    {
        $settings = emailSettings($id);
        $smtpDetail = config([
            'mail.mailers.smtp.transport' => $settings['SERVER_DRIVER'],
            'mail.mailers.smtp.host' => $settings['SERVER_HOST'],
            'mail.mailers.smtp.port' => $settings['SERVER_PORT'],
            'mail.mailers.smtp.encryption' => $settings['SERVER_ENCRYPTION'],
            'mail.mailers.smtp.username' => $settings['SERVER_USERNAME'],
            'mail.mailers.smtp.password' => $settings['SERVER_PASSWORD'],
            'mail.from.address' => $settings['FROM_EMAIL'],
            'mail.from.name' => $settings['FROM_NAME'],
        ]);
        return $smtpDetail;
    }
}

if (!function_exists('planPrefix')) {
    function planPrefix()
    {
        $settings = settings();
        return $settings["plan_number_prefix"];
    }
}

if (!function_exists('eventPrefix')) {
    function eventPrefix()
    {
        $settings = settings();
        return $settings["event_number_prefix"];
    }
}

if (!function_exists('suspensionPrefix')) {
    function suspensionPrefix()
    {
        $settings = settings();
        return $settings["suspension_number_prefix"];
    }
}

if (!function_exists('paymentPrefix')) {
    function paymentPrefix()
    {
        $settings = settings();
        return $settings["payment_number_prefix"];
    }
}

if (!function_exists('expensePrefix')) {
    function expensePrefix()
    {
        $settings = settings();
        return $settings["expense_number_prefix"];
    }
}

if (!function_exists('invoicePrefix')) {
    function invoicePrefix()
    {
        $settings = settings();
        return $settings["invoice_number_prefix"];
    }
}

if (!function_exists('memberPrefix')) {
    function memberPrefix()
    {
        $settings = settings();
        return $settings["member_prefix"];
    }
}

if (!function_exists('timeCalculation')) {
    function timeCalculation($startDate, $startTime, $endDate, $endTime)
    {
        $startdate = $startDate . ' ' . $startTime;
        $enddate = $endDate . ' ' . $endTime;
        $startDateTime = new DateTime($startdate);
        $endDateTime = new DateTime($enddate);
        $interval = $startDateTime->diff($endDateTime);
        $totalHours = $interval->h + $interval->i / 60;
        return number_format($totalHours, 2);
    }
}

if (!function_exists('setup')) {
    function setup()
    {
        $setupPath = storage_path() . "/installed";
        return $setupPath;
    }
}

if (!function_exists('userLoggedHistory')) {
    function userLoggedHistory()
    {
        // Only log for authenticated users; guests have no user context.
        if (!Auth::check()) {
            return;
        }

        $serverip = $_SERVER['REMOTE_ADDR'];
        $data = @unserialize(file_get_contents('http://ip-api.com/php/' . $serverip));
        if (isset($data['status']) && $data['status'] == 'success') {
            $browser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
            if ($browser->device->type == 'bot') {
                return redirect()->intended(RouteServiceProvider::HOME);
            }
            $referrerData = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : null;
            $data['browser'] = $browser->browser->name ?? null;
            $data['os'] = $browser->os->name ?? null;
            $data['language'] = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
            $data['device'] = User::getDevice($_SERVER['HTTP_USER_AGENT']);
            $data['referrer_host'] = !empty($referrerData['host']);
            $data['referrer_path'] = !empty($referrerData['path']);
            $result = json_encode($data);
            $details = new LoggedHistory();
            $details->type = Auth::user()->type ?? null;
            $details->user_id = Auth::user()->id;
            $details->date = date('Y-m-d H:i:s');
            $details->Details = $result;
            $details->ip = $serverip;
            $details->parent_id = parentId();
            $details->save();
        }
    }
}

if (!function_exists('settingsById')) {
    function settingsById($userId)
    {
        $data = DB::table('settings');
        $data = $data->where('parent_id', $userId);
        $data = $data->get();
        $settings = settingsKeys();
        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }
        config([
            'captcha.secret' => $settings['recaptcha_key'],
            'captcha.sitekey' => $settings['recaptcha_secret'],
            'options' => [
                'timeout' => 30,
            ],
        ]);
        return $settings;
    }
}

if (!function_exists('defaultTemplateList')) {
    function defaultTemplateList()
    {
        return [
            'user_create' => [
                'module' => 'user_create',
                'name' => 'New User',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{new_user_name}', '{app_link}', '{username}', '{password}'],
                'subject' => 'Welcome to {company_name}',
                'templete' => ' 
 <p><strong>Dear {new_user_name}</strong>,</p><p> </p><blockquote><p>Welcome to {company_name}! We are excited to have you on board and look forward to providing you with an exceptional experience.</p><p>We hope you enjoy your experience with us. If you have any feedback, feel free to share it with us.</p><p> </p><p>Your account details are as follows:</p><p><strong>App Link:</strong> <a href="{app_link}">{app_link}</a></p><p><strong>Username:</strong> {username}</p><p><strong>Password:</strong> {password}</p><p> </p><p>Thank you for choosing .</p></blockquote>',
                'sms_message' => 'Hi {new_user_name},
 welcome to {company_name}!
 App: {app_link}
 Username: {username}
 Password: {password}',
            ],
            'member_create' => [
                'module' => 'member_create',
                'name' => 'New member',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{member_name}', '{member_number}', '{membership_plan}', '{membership_start_date}', '{membership_expiry_date}', '{membership_status}'],
                'subject' => 'Welcome to {company_name}!',
                'templete' => ' 
 <p><strong>Dear {member_name},</strong></p><p>Welcome to <strong>{company_name}</strong>! We are thrilled to have you join our community. Our team is dedicated to providing you with the best experience possible.</p><p>Your membership details are as follows:</p><ul><li><strong>Membership Plan:</strong> {membership_plan}</li><li><strong>Membership Start Date:</strong> {membership_start_date}</li><li><strong>Membership Expiry Date:</strong> {membership_expiry_date}</li><li><strong>Membership Status:</strong> {membership_status}</li></ul><p>If you have any questions or need assistance, feel free to contact us at <strong>{company_email}</strong> or call us at <strong>{company_phone_number}</strong>.</p><p>Thank you for choosing <strong>{company_name}</strong>. We look forward to serving you!</p><p>Best regards,</p><p><strong>{company_name} Team</strong></p> 
 ',
                'sms_message' => '{company_name}: Dear {member_name}, welcome to {company_name}! Your plan {membership_plan} starts on {membership_start_date} and expires on {membership_expiry_date}. Status: {membership_status}.',
            ],
            'event_create' => [
                'module' => 'event_create',
                'name' => 'New Event',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{event_name}', '{event_date_time}', '{event_location}', '{max_participant}', '{registration_deadline}', '{availability_status}'],
                'subject' => 'New Event Announcement: {event_name}',
                'templete' => ' 
 <p><strong>Exciting News from {company_name}!</strong></p><p>We are excited to announce a brand-new event:</p><ul><li><strong>Event Name:</strong> {event_name}</li><li><strong>Date & Time:</strong> {event_date_time}</li><li><strong>Location:</strong> {event_location}</li><li><strong>Registration Deadline:</strong> {registration_deadline}</li><li><strong>Availability Status:</strong> {availability_status}</li></ul><p><strong>Important:</strong> Be sure to register before the deadline, <strong>{registration_deadline}</strong>, to secure your spot.</p><p>Don’t miss this opportunity to engage and connect! For any inquiries, please reach out to us at <strong>{company_email}</strong> or call us at <strong>{company_phone_number}</strong>.</p><p>Thank you for being a part of <strong>{company_name}</strong>. We look forward to your participation!</p><p>Best regards,</p><p><strong>{company_name} Team</strong></p> 
 ',
                'sms_message' => '{company_name}: New Event - {event_name} on {event_date_time} at {event_location}. Register by {registration_deadline}. Status: {availability_status}.',
            ],
            'activity_tracking' => [
                'module' => 'activity_tracking',
                'name' => 'Activity tracking',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{member_name}', '{event_name}', '{check_in}', '{check_out}', '{duration}'],
                'subject' => 'Activity Summary for {event_name}',
                'templete' => ' 
 <p><strong>Activity Summary from {company_name}</strong></p><p>Dear {member_name},</p><p>Here is the summary of your recent activity for the event <strong>{event_name}</strong>:</p><ul><li><strong>Event Name:</strong> {event_name}</li><li><strong>Check-In Time:</strong> {check_in}</li><li><strong>Check-Out Time:</strong> {check_out}</li><li><strong>Total Duration:</strong> {duration}</li></ul><p>We hope you had a great experience during the event! If you have any questions or feedback, feel free to reach out to us at <strong>{company_email}</strong> or call us at <strong>{company_phone_number}</strong>.</p><p>Thank you for being a part of <strong>{company_name}</strong>. We look forward to your continued participation!</p><p>Best regards,</p><p><strong>{company_name} Team</strong></p> 
 ',
                'sms_message' => '{company_name}: Hi {member_name}, your activity for {event_name} - Check-In: {check_in}, Check-Out: {check_out}, Duration: {duration}.',
            ],
            'membership_suspension' => [
                'module' => 'membership_suspension',
                'name' => 'Membership Suspension',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{member_name}', '{start_date}', '{end_date}', '{status}', '{suspension_reason}'],
                'subject' => 'Notice of Membership Suspension',
                'templete' => ' 
 <p><strong>Notice of Membership Suspension</strong></p><p>Dear {member_name},</p><p>We regret to inform you that your membership with <strong>{company_name}</strong> has been suspended. Below are the details of the suspension:</p><ul><li><strong>Suspension Status:</strong> {status}</li><li><strong>Suspension Start Date:</strong> {start_date}</li><li><strong>Suspension End Date:</strong> {end_date}</li><li><strong>Reason for Suspension:</strong> {suspension_reason}</li></ul><p>During this time, you will not have access to membership benefits or services. If you believe this suspension is in error or you have further inquiries, please do not hesitate to contact us at <strong>{company_email}</strong> or call <strong>{company_phone_number}</strong>.</p><p>We value your membership and hope to resolve this matter as soon as possible.</p><p>Thank you for your understanding.</p><p>Best regards,</p><p><strong>{company_name} Team</strong></p> 
 ',
                'sms_message' => '{company_name}: Dear {member_name}, your membership has been suspended from {start_date} to {end_date}. Reason: {suspension_reason}. Status: {status}.',
            ],
            'payment_create' => [
                'module' => 'payment_create',
                'name' => 'New Payment',
                'short_code' => ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{member_name}', '{payment_number}', '{payment_date}', '{plan_name}', '{plan_number}', '{amount}', '{payment_method}'],
                'subject' => 'Payment Confirmation - {payment_number}',
                'templete' => ' 
 <p><strong>Payment Confirmation from {company_name}</strong></p><p>Dear {member_name},</p><p>We are pleased to inform you that your payment has been successfully processed. Below are the payment details:</p><ul><li><strong>Payment Number:</strong> {payment_number}</li><li><strong>Payment Date:</strong> {payment_date}</li><li><strong>Plan Name:</strong> {plan_name}</li><li><strong>Plan Number:</strong> {plan_number}</li><li><strong>Amount Paid:</strong> {company_currency} {amount}</li><li><strong>Payment Method:</strong> {payment_method}</li></ul><p>Thank you for your prompt payment. If you have any questions or require further assistance, please do not hesitate to contact us at <strong>{company_email}</strong> or call us at <strong>{company_phone_number}</strong>.</p><p>Our office is located at: <strong>{company_address}</strong>.</p><p>We appreciate your continued trust in <strong>{company_name}</strong>.</p><p>Best regards,</p><p><strong>{company_name} Team</strong></p> 
 ',
                'sms_message' => '{company_name}: Dear {member_name}, your payment {payment_number} of {company_currency} {amount} for {plan_name} on {payment_date} via {payment_method} has been received. Thank you!',
            ],
        ];
    }
}

if (!function_exists('defaultTemplate')) {
    function defaultTemplate($id)
    {
        $templateData = defaultTemplateList();
        $createdTemplates = [];
        foreach ($templateData as $key => $value) {
            $template = new Notification();
            $template->module = $value['module'];
            $template->name = $value['name'];
            $template->subject = $value['subject'];
            $template->message = $value['templete'];
            $template->short_code = json_encode($value['short_code']);
            $template->enabled_email = 0;
            $template->parent_id = $id;
            if (!empty($value['sms_message'])) {
                $template->sms_message = $value['sms_message'];
            }
            $template->enabled_sms = 0;
            $template->save();
            $createdTemplates[] = $template;
        }
        return $createdTemplates;
    }
}

if (!function_exists('defaultSMSTemplate')) {
    function defaultSMSTemplate()
    {
        $templateData = defaultTemplateList();
        $createdTemplates = [];
        foreach ($templateData as $key => $value) {
            $Users = User::where('type', 'owner')->get();
            foreach ($Users as $User) {
                $template = Notification::where('module', $value['module'])->where('parent_id', $User->id)->first();
                if (empty($template)) {
                    $template = new Notification();
                    $template->module = $value['module'];
                    $template->name = $value['name'];
                    $template->subject = $value['subject'];
                    $template->message = $value['templete'];
                    $template->short_code = json_encode($value['short_code']);
                    $template->enabled_email = 0;
                    $template->enabled_sms = 0;
                    $template->parent_id = $User->id;
                    $template->save();
                }
            }
            Notification::where('module', $value['module'])->whereNull('sms_message')->update(['sms_message' => $value['sms_message'], 'enabled_sms' => 0]);
            $createdTemplates[] = $value;
        }
        return $createdTemplates;
    }
}

if (!function_exists('send_twilio_msg')) {
    function send_twilio_msg($to, $msg)
    {
        if (!empty($msg)) {
            $settings = settings();
            $sid = $settings['twilio_sid'];
            $token = $settings['twilio_token'];
            $from_number = $settings['twilio_from_number'];
            try {
                $client = new Client($sid, $token);
                $client->messages->create($to, [
                    'from' => $from_number,
                    'body' => $msg,
                ]);
            } catch (\Exception $e) {
                Log::error('Twilio SMS send failed: ' . $e->getMessage());
            }
        }
    }
}

if (!function_exists('MessageReplace')) {
    function MessageReplace($notification, $id = 0)
    {
        $return['subject'] = $notification->subject;
        $return['message'] = $notification->message;
        $return['sms_message'] = $notification->sms_message;
        if (!empty($notification->password)) {
            $notification['password'] = $notification->password;
        }
        $settings = settings();
        if (!empty($notification)) {
            $search = [];
            $replace = [];
            if ($notification->module == 'user_create') {
                $user = User::find($id);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{new_user_name}', '{app_link}', '{username}', '{password}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $user->name, env('APP_URL'), $user->email, $notification['password']];
            }
            if ($notification->module == 'member_create') {
                $member = Member::find($id);
                $memberShip = Membership::where('member_id', $id)->where('parent_id', parentId())->first();
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{member_name}', '{member_number}', '{membership_plan}', '{membership_start_date}', '{membership_expiry_date}', '{membership_status}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $member->first_name . ' ' . $member->last_name, memberPrefix() . $member->member_id, !empty($memberShip->plans) ? $memberShip->plans->plan_name : '', !empty($memberShip) ? dateFormat($memberShip->start_date) : '', !empty($memberShip) ? dateFormat($memberShip->expiry_date) : '', !empty($memberShip) ? $memberShip->status : ''];
            }
            if ($notification->module == 'event_create') {
                $event = Event::find($id);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{event_name}', '{event_date_time}', '{event_location}', '{max_participant}', '{registration_deadline}', '{availability_status}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $event->event_name, dateFormat($event->date_time), $event->location, $event->max_participant, dateFormat($event->registration_deadline), $event->availability_status];
            }
            if ($notification->module == 'activity_tracking') {
                $activity = ActivityTracking::find($id);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{member_name}', '{event_name}', '{check_in}', '{check_out}', '{duration}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $activity->members->first_name . ' ' . $activity->members->last_name, $activity->events->event_name, dateFormat($activity->check_in) . ' ' . timeFormat($activity->check_in), dateFormat($activity->check_out) . ' ' . timeFormat($activity->check_out), $activity->duration];
            }
            if ($notification->module == 'membership_suspension') {
                $suspension = MembershipSuspension::find($id);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{member_name}', '{start_date}', '{end_date}', '{status}', '{suspension_reason}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $suspension->members->first_name . ' ' . $suspension->members->last_name, dateFormat($suspension->start_date), dateFormat($suspension->end_date), $suspension->status, $suspension->reason];
            }
            if ($notification->module == 'payment_create') {
                $payment = MembershipPayment::find($id);
                $search = [
                    '{company_name}',
                    '{company_email}',
                    '{company_phone_number}',
                    '{company_address}',
                    '{company_currency}',
                    '{member_name}',
                    '{payment_number}',
                    '{payment_date}',
                    '{plan_name}',
                    '{plan_number}',
                    '{amount}',
                    '{payment_method}'
                ];
                $replace = [
                    $settings['company_name'],
                    $settings['company_email'],
                    $settings['company_phone'],
                    $settings['company_address'],
                    $settings['CURRENCY_SYMBOL'],
                    $payment->members->first_name . ' ' . $payment->members->last_name,
                    paymentPrefix() . $payment->payment_id,
                    dateFormat($payment->payment_date),
                    $payment->plans->plan_name,
                    planPrefix() . $payment->plans->plan_id,
                    $payment->amount,
                    $payment->payment_method
                ];
            }
            $return['subject'] = str_replace($search, $replace, $notification->subject);
            $return['message'] = str_replace($search, $replace, $notification->message);
            $return['sms_message'] = str_replace($search, $replace, $notification->sms_message);
        }
        return $return;
    }
}

if (!function_exists('sendEmail')) {
    function sendEmail($to, $datas)
    {
        $datas['settings'] = settings();
        try {
            emailSettings(parentId());
            Mail::to($to)->send(new TestMail($datas));
            return [
                'status' => 'success',
                'message' => __('Email successfully sent'),
            ];
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return [
                'status' => 'error',
                'message' => __('We noticed that the email settings have not been configured for this system. As a result, email-related functionalities may not work as expected. please add valide email smtp details first.')
            ];
        }
    }
}

if (!function_exists('commonEmailSend')) {
    function commonEmailSend($to, $datas)
    {
        $datas['settings'] = settings();
        try {
            if (Auth::check()) {
                if ($datas['module'] == 'owner_create') {
                    emailSettings(1);
                } else {
                    emailSettings(parentId());
                }
            } else {
                emailSettings($datas['parent_id']);
            }
            Mail::to($to)->send(new Common($datas));
            return [
                'status' => 'success',
                'message' => __('Email successfully sent'),
            ];
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return [
                'status' => 'error',
                'message' => __('We noticed that the email settings have not been configured for this system. As a result, email-related functionalities may not work as expected. please add valide email smtp details first.')
            ];
        }
    }
}

if (!function_exists('emailSettings')) {
    function emailSettings($id)
    {
        $settingData = DB::table('settings')
            ->where('type', 'smtp')
            ->where('parent_id', $id)
            ->get();
        $result = [
            'FROM_EMAIL' => "",
            'FROM_NAME' => "",
            'SERVER_DRIVER' => "",
            'SERVER_HOST' => "",
            'SERVER_PORT' => "",
            'SERVER_USERNAME' => "",
            'SERVER_PASSWORD' => "",
            'SERVER_ENCRYPTION' => "",
        ];
        foreach ($settingData as $setting) {
            $result[$setting->name] = $setting->value;
        }
        config([
            'mail.default' => $result['SERVER_DRIVER'] ?? '',
            'mail.mailers.smtp.host' => $result['SERVER_HOST'] ?? '',
            'mail.mailers.smtp.port' => $result['SERVER_PORT'] ?? '',
            'mail.mailers.smtp.encryption' => $result['SERVER_ENCRYPTION'] ?? '',
            'mail.mailers.smtp.username' => $result['SERVER_USERNAME'] ?? '',
            'mail.mailers.smtp.password' => $result['SERVER_PASSWORD'] ?? '',
            'mail.from.name' => $result['FROM_NAME'] ?? '',
            'mail.from.address' => $result['FROM_EMAIL'] ?? '',
        ]);
        return $result;
    }
}

if (!function_exists('sendEmailVerification')) {
    function sendEmailVerification($to, $data)
    {
        $data['settings'] = emailSettings(1);
        try {
            Mail::to($to)->send(new EmailVerification($data));
            return [
                'status' => 'success',
                'message' => __('Email successfully sent'),
            ];
        } catch (\Exception $e) {
            Log::error('Email Sending Failed: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => __('We noticed that the email settings have not been configured for this system. As a result, email-related functionalities may not work as expected. please contact the administrator to resolve this issue.')
            ];
        }
    }
}

if (!function_exists('RoleName')) {
    function RoleName($permission_id = '0')
    {
        $retuen = '';
        $role_id_array = DB::table('role_has_permissions')->where('permission_id', $permission_id)->pluck('role_id');
        if (!empty($role_id_array)) {
            $role_id_array = DB::table('roles')->whereIn('id', $role_id_array)->pluck('name')->toArray();
            $retuen = implode(', ', $role_id_array);
        }
        return $retuen;
    }
}

if (!function_exists('HomePageSection')) {
    function HomePageSection()
    {
        $retuen = [
            [
                'title' => 'Header Menu',
                'section' => 'Section 0',
                'content_value' => '{"name":"Header Menu","menu_pages":["1","2"]}',
            ],
            [
                'title' => 'Banner',
                'section' => 'Section 1',
                'content_value' => '{"name":"Banner","section_enabled":"active","title":"Clublink SaaS - Membership Management Software","sub_title":"Membership Management Software is a powerful tool designed to help organizations streamline and automate all aspects of managing their members.This software simplifies essential tasks such as member registration, renewals, payment processing, attendance tracking, events, and reporting.","btn_name":"Get Started","btn_link":"#","section_footer_text":"Manage your business efficiently with our all-in-one solution designed for performance, security, and scalability.","section_footer_image":{},"section_main_image":{},"section_footer_image_path":"upload/homepage/banner_2.png","section_main_image_path":"upload/homepage/banner_1.png","box_image_1_path":"","box_image_2_path":"","box_image_3_path":"","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}',
            ],
            [
                'title' => 'OverView',
                'section' => 'Section 2',
                'content_value' => '{"name":"OverView","section_enabled":"active","Box1_title":"Customers","Box1_number":"500+","Box2_title":"Subscription Plan","Box2_number":"4+","Box3_title":"Language","Box3_number":"11+","box1_number_image":{},"box2_number_image":{},"box3_number_image":{},"section_footer_image_path":"","section_main_image_path":"","box_image_1_path":"upload/homepage/OverView_1.svg","box_image_2_path":"upload/homepage/OverView_2.svg","box_image_3_path":"upload/homepage/OverView_3.svg","Box1_image_path":"","Box2_image_path":"","Sec4_box1_image_path":"","Sec4_box2_image_path":"","Sec4_box3_image_path":"","Sec4_box4_image_path":"","Sec4_box5_image_path":"","Sec4_box6_image_path":"","Sec7_box1_image_path":"","Sec7_box2_image_path":"","Sec7_box3_image_path":"","Sec7_box4_image_path":"","Sec7_box5_image_path":"","Sec7_box6_image_path":"","Sec7_box7_image_path":"","Sec7_box8_image_path":""}',
            ],
            // ... (remaining sections unchanged from your original)
        ];
        foreach ($retuen as $key => $value) {
            $HomePage = new HomePage();
            $HomePage->title = $value['title'];
            $HomePage->section = $value['section'];
            if (!empty($value['content_value'])) {
                $HomePage->content_value = $value['content_value'];
            }
            $HomePage->enabled = 1;
            $HomePage->parent_id = 1;
            $HomePage->save();
        }
        return '';
    }
}

if (!function_exists('CustomPage')) {
    function CustomPage()
    {
        $retuen = [
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy_policy',
                'content' => "<h3><strong>1. Information We Collect</strong></h3><p>We may collect the following types of information from you:</p><h4><strong>a. Personal Information</strong></h4><ul><li>Name, email address, phone number, and other contact details.</li><li>Payment information (if applicable).</li></ul><h4><strong>b. Non-Personal Information</strong></h4><ul><li>Browser type, operating system, and device information.</li><li>Usage data, including pages visited, time spent, and other analytical data.</li></ul><h4><strong>c. Information You Provide</strong></h4><ul><li>Information you voluntarily provide when contacting us, signing up, or completing forms.</li></ul><h4><strong>d. Cookies and Tracking Technologies</strong></h4><ul><li>We use cookies, web beacons, and other tracking tools to enhance your experience and analyze usage patterns.</li></ul><h3><strong>2. How We Use Your Information</strong></h3><p>We use the information collected for the following purposes:</p><ul><li>To provide, maintain, and improve our Services.</li><li>To process transactions and send you confirmations.</li><li>To communicate with you, including responding to inquiries or providing updates.</li><li>To personalize your experience and deliver tailored content.</li><li>To comply with legal obligations and protect against fraud or misuse.</li></ul><h3><strong>3. How We Share Your Information</strong></h3><p>We do not sell your personal information. However, we may share your information with:</p><ul><li><strong>Service Providers:</strong> Third-party vendors who assist in providing our Services.</li><li><strong>Legal Authorities:</strong> When required to comply with legal obligations or protect our rights.</li><li><strong>Business Transfers:</strong> In the event of a merger, acquisition, or sale of assets, your information may be transferred.</li></ul><h3><strong>4. Data Security</strong></h3><p>We implement appropriate technical and organizational measures to protect your data against unauthorized access, disclosure, alteration, or destruction. However, no method of transmission or storage is 100% secure, and we cannot guarantee absolute security.</p><h3><strong>5. Your Rights</strong></h3><p>You have the right to:</p><ul><li>Access, correct, or delete your personal data.</li><li>Opt-out of certain data processing activities, including marketing communications.</li><li>Withdraw consent where processing is based on consent.</li></ul><p>To exercise your rights, please contact us at [contact email].</p><h3><strong>6. Third-Party Links</strong></h3><p>Our Services may contain links to third-party websites. We are not responsible for the privacy practices or content of these websites. Please review their privacy policies before engaging with them.</p><h3><strong>7. Children's Privacy</strong></h3><p>Our Services are not intended for children under the age of [13/16], and we do not knowingly collect personal information from them. If we become aware that a child has provided us with personal data, we will take steps to delete it.</p><h3><strong>8. Changes to This Privacy Policy</strong></h3><p>We may update this Privacy Policy from time to time. Any changes will be posted on this page with a revised 'Last Updated' date. Your continued use of the Services after such changes constitutes your acceptance of the new terms.</p><h3> </h3>"
            ],
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms_conditions',
                'content' => "<h3><strong>1. Acceptance of Terms</strong></h3><p>By using our Services, you confirm that you are at least [18 years old or the legal age in your jurisdiction] and capable of entering into a binding agreement. If you are using our Services on behalf of an organization, you represent that you have the authority to bind that organization to these Terms.</p><h3><strong>2. Use of Services</strong></h3><p>You agree to use our Services only for lawful purposes and in accordance with these Terms. You must not:</p><ul><li>Violate any applicable laws or regulations.</li><li>Use our Services in a manner that could harm, disable, overburden, or impair them.</li><li>Attempt to gain unauthorized access to our systems or networks.</li><li>Transmit any harmful code, viruses, or malicious software.</li></ul><h3><strong>3. User Accounts</strong></h3><p>If you create an account with us, you are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. You agree to notify us immediately of any unauthorized use of your account or breach of security.</p><h3><strong>4. Intellectual Property</strong></h3><p>All content, trademarks, logos, and intellectual property associated with our Services are owned by [Your Company Name] or our licensors. You are granted a limited, non-exclusive, non-transferable license to access and use the Services for personal or authorized business purposes. Any unauthorized use, reproduction, or distribution is prohibited.</p><h3><strong>5. Payment and Billing</strong> (if applicable)</h3><p>If our Services involve payments:</p><ul><li>All fees are due at the time of purchase unless otherwise agreed.</li><li>We reserve the right to change pricing or introduce new fees with prior notice.</li><li>Refunds, if applicable, will be handled according to our [Refund Policy].</li></ul><h3><strong>6. Termination of Services</strong></h3><p>We reserve the right to suspend or terminate your access to our Services at our discretion, without prior notice, if:</p><ul><li>You breach these Terms.</li><li>We are required to do so by law.</li><li>Our Services are discontinued or altered.</li></ul><h3><strong>7. Limitation of Liability</strong></h3><p>To the fullest extent permitted by law:</p><ul><li>[Your Company Name] and its affiliates shall not be liable for any direct, indirect, incidental, or consequential damages resulting from your use of our Services.</li><li>Our liability is limited to the amount you paid, if any, for accessing our Services.</li></ul><h3><strong>8. Indemnification</strong></h3><p>You agree to indemnify and hold [Your Company Name], its affiliates, employees, and partners harmless from any claims, liabilities, damages, losses, or expenses arising from your use of the Services or violation of these Terms.</p><h3><strong>9. Modifications to Terms</strong></h3><p>We may update these Terms from time to time. Any changes will be effective immediately upon posting, and your continued use of the Services constitutes your acceptance of the revised Terms.</p>"
            ],
        ];
        foreach ($retuen as $key => $value) {
            $Page = new Page();
            $Page->title = $value['title'];
            $Page->slug = $value['slug'];
            $Page->content = $value['content'];
            $Page->enabled = 1;
            $Page->parent_id = 1;
            $Page->save();
        }
        $FAQ_retuen = [
            [
                'question' => 'What features does your software offer?',
                'description' => 'Our software provides a range of features including automation tools, real-time analytics, cloud-based access, secure data storage, seamless integrations, and customizable solutions tailored to your business needs.',
            ],
            [
                'question' => 'Is your software easy to use?',
                'description' => 'Yes! Our platform is designed to be user-friendly and intuitive, so your team can get started quickly without a steep learning curve.',
            ],
            [
                'question' => 'Can I integrate your software with my existing systems?',
                'description' => 'Absolutely! Our software is built to easily integrate with your current tools and systems, making the transition seamless and efficient.',
            ],
            [
                'question' => 'Is customer support available?',
                'description' => 'Yes! We offer 24/7 customer support. Our dedicated team is ready to assist you with any questions or issues you may have.',
            ],
            [
                'question' => 'Is my data secure with your software?',
                'description' => 'Yes. We use advanced encryption and data protection protocols to ensure your data is secure and private at all times.',
            ],
            [
                'question' => 'Can I customize the software to fit my business needs?',
                'description' => 'Yes! Our software is highly customizable to adapt to your unique workflows and requirements.',
            ],
            [
                'question' => 'What types of businesses can benefit from your software?',
                'description' => 'Our solutions are suitable for a wide range of industries, including retail, healthcare, finance, marketing, and more. We tailor our offerings to meet the specific needs of each business.',
            ],
            [
                'question' => 'Is there a free trial available?',
                'description' => 'Yes! We offer a free trial so you can explore the features and capabilities of our software before committing.',
            ],
            [
                'question' => 'Do I need technical expertise to use the software?',
                'description' => 'Not at all. Our software is designed for users of all skill levels. Plus, our support team is available to guide you through any setup or usage questions.',
            ],
            [
                'question' => 'How often is the software updated?',
                'description' => 'We regularly release updates to improve features, security, and overall performance, ensuring that you always have access to the latest technology.',
            ],
        ];
        foreach ($FAQ_retuen as $key => $FAQ_value) {
            $FAQs = new FAQ();
            $FAQs->question = $FAQ_value['question'];
            $FAQs->description = $FAQ_value['description'];
            $FAQs->enabled = 1;
            $FAQs->parent_id = 1;
            $FAQs->save();
        }
        return '';
    }
}

if (!function_exists('DefaultCustomPage')) {
    function DefaultCustomPage()
    {
        $return = Page::where('enabled', 1)->whereIn('id', [1, 2])->get();
        return $return;
    }
}

if (!function_exists('DefaultBankTransferPayment')) {
    function DefaultBankTransferPayment()
    {
        $bankArray = [
            'bank_transfer_payment' => 'on',
            'bank_name' => 'Bank of America',
            'bank_holder_name' => 'SmartWeb Infotech',
            'bank_account_number' => '4242 4242 4242 4242',
            'bank_ifsc_code' => 'BOA45678',
            'bank_other_details' => '',
        ];
        foreach ($bankArray as $key => $val) {
            \DB::insert(
                'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $val,
                    $key,
                    'payment',
                    1,
                ]
            );
        }
        return '';
    }
}

if (!function_exists('QrCode2FA')) {
    function QrCode2FA()
    {
        $user = Auth::user();
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $company = env('APP_NAME');
        if ($user->type != 'super admin') {
            $company = isset(settings()['company_name']) && !empty(settings()['company_name']) ? settings()['company_name'] : $company;
        }
        $qr_code = $google2fa->getQRCodeInline(
            $company,
            $user->email,
            $secret
        );
        session(["2fa_secret" => $secret]);
        return $qr_code;
    }
}

if (!function_exists('authPage')) {
    function authPage($id)
    {
        $templateData = [
            'title' => [
                "Secure Access, Seamless Experience.",
                "Your Trusted Gateway to Digital Security.",
                "Fast, Safe & Effortless Login."
            ],
            'description' => [
                "Securely access your account with ease. Whether you're logging in, signing up, or resetting your password, we ensure a seamless and protected experience. Your data, your security, our priority.",
                "Fast, secure, and hassle-free authentication. Sign in with confidence and experience a seamless way to access your account—because your security matters.",
                "A seamless and secure way to access your account. Whether you're logging in, signing up, or recovering your password, we ensure your data stays protected at every step."
            ],
        ];
        $authPage = new AuthPage();
        $authPage->title = json_encode($templateData['title']);
        $authPage->description = json_encode($templateData['description']);
        $authPage->section = 1;
        $authPage->image = 'upload/images/auth_page.svg';
        $authPage->parent_id = $id;
        $authPage->save();
        $createdTemplates[] = $authPage;
        return $createdTemplates;
    }
}

if (!function_exists('paymentNumber')) {
    function paymentNumber()
    {
        $latest = MembershipPayment::where('parent_id', parentId())->latest()->first();
        if ($latest == null) {
            return 1;
        } else {
            return $latest->payment_id + 1;
        }
    }
}

if (!function_exists('NewPermission')) {
    function NewPermission()
    {
        $owners = User::where('type', 'owner')->get();
        foreach ($owners as $key => $value) {
            Role::firstOrCreate([
                'name' => 'member',
                'parent_id' => $value->id,
            ]);
        }
        $permissions = [
            ['name' => 'manage twilio settings', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage payment settings', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage account settings', 'guard_name' => 'web', 'roles' => ['member']],
            ['name' => 'manage password settings', 'guard_name' => 'web', 'roles' => ['member']],
            ['name' => 'manage 2FA settings', 'guard_name' => 'web', 'roles' => ['member']],
            ['name' => 'manage membership', 'guard_name' => 'web', 'roles' => ['member']],
            ['name' => 'show membership', 'guard_name' => 'web', 'roles' => ['member']],
            ['name' => 'manage membership payment', 'guard_name' => 'web', 'roles' => ['member']],
            ['name' => 'show membership payment', 'guard_name' => 'web', 'roles' => ['member']],
            ['name' => 'manage event', 'guard_name' => 'web', 'roles' => ['member']],
            ['name' => 'manage activity tracking', 'guard_name' => 'web', 'roles' => ['member']],
            ['name' => 'show activity tracking', 'guard_name' => 'web', 'roles' => ['member']],
            ['name' => 'manage membership suspension', 'guard_name' => 'web', 'roles' => ['member']],
            ['name' => 'show membership suspension', 'guard_name' => 'web', 'roles' => ['member']],
            ['name' => 'manage membership plan', 'guard_name' => 'web', 'roles' => ['member']],
            ['name' => 'manage income report', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage membership report', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage expense report', 'guard_name' => 'web', 'roles' => ['owner']],
        ];
        if (!empty($permissions)) {
            foreach ($permissions as $permData) {
                Permission::firstOrCreate([
                    'name' => $permData['name'],
                    'guard_name' => $permData['guard_name']
                ]);
            }
            $permissionsByRole = [];
            foreach ($permissions as $permData) {
                foreach ($permData['roles'] as $roleName) {
                    $permissionsByRole[$roleName][] = $permData['name'];
                }
            }
            foreach ($permissionsByRole as $roleName => $permNames) {
                $roles = Role::where('name', $roleName)->get();
                foreach ($roles as $role) {
                    $role->givePermissionTo($permNames);
                }
            }
        }
        $removePermissions = [
            ['name' => 'create note', 'guard_name' => 'web', 'roles' => ['member', 'manager']],
            ['name' => 'edit note', 'guard_name' => 'web', 'roles' => ['member', 'manager']],
            ['name' => 'delete note', 'guard_name' => 'web', 'roles' => ['member', 'manager']],
            ['name' => 'show note', 'guard_name' => 'web', 'roles' => ['member', 'manager']],
            ['name' => 'manage membership', 'guard_name' => 'web', 'roles' => ['owner']],
            ['name' => 'manage membership payment', 'guard_name' => 'web', 'roles' => ['owner']],
        ];
        foreach ($removePermissions as $permData) {
            $permission = Permission::where('name', $permData['name'])
                ->where('guard_name', $permData['guard_name'])
                ->first();
            if ($permission) {
                foreach ($permData['roles'] as $roleName) {
                    $roles = Role::where('name', $roleName)->get();
                    foreach ($roles as $role) {
                        $role->revokePermissionTo($permission);
                    }
                }
            }
        }
        defaultSMSTemplate();
        return true;
    }
}

