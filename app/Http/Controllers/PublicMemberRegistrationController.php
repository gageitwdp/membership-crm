<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\MembershipPayment;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PublicMemberRegistrationController extends Controller
{
    /**
     * Show the public registration form
     */
    public function create()
    {
        // Get available membership plans for user ID 2 (owner)
        $membershipPlans = MembershipPlan::where('parent_id', 2)->get();
        
        return view('public.register', compact('membershipPlans'));
    }

    /**
     * Handle the public registration submission
     */
    public function store(Request $request)
    {
        // Build validation rules conditionally based on registration type
        $rules = [
            'registration_type' => 'required|in:self,parent',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'required|string',
            'dob' => 'required|date',
            'address' => 'required|string',
            'gender' => 'required|in:Male,Female',
            'plan_id' => 'nullable|exists:membership_plans,id',
        ];

        $messages = [
            'registration_type.required' => __('Please select whether you are registering yourself or a child.'),
        ];

        // Add conditional validation based on registration type
        if ($request->registration_type === 'self') {
            $rules['age_confirmation'] = 'required|accepted';
            $messages['age_confirmation.required'] = __('You must confirm that you are 18 years or older to register yourself.');
            $messages['age_confirmation.accepted'] = __('You must confirm that you are 18 years or older to register yourself.');
        } elseif ($request->registration_type === 'parent') {
            $rules['parent_first_name'] = 'required|string|max:255';
            $rules['parent_last_name'] = 'required|string|max:255';
            $rules['parent_email'] = 'required|email';
            $rules['parent_phone'] = 'required|string';
            $messages['parent_first_name.required'] = __('Parent/Guardian first name is required when registering a child.');
            $messages['parent_last_name.required'] = __('Parent/Guardian last name is required when registering a child.');
            $messages['parent_email.required'] = __('Parent/Guardian email is required when registering a child.');
            $messages['parent_phone.required'] = __('Parent/Guardian phone is required when registering a child.');
        }

        $validator = \Validator::make(
            $request->all(),
            $rules,
            $messages
        );

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Get the member role for parent_id = 2
            $userRole = Role::where('name', 'member')
                ->where('parent_id', 2)
                ->first();

            if (!$userRole) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => __('Member role not found. Please contact the administrator.')], 400);
                }
                return redirect()->back()
                    ->with('error', __('Member role not found. Please contact the administrator.'))
                    ->withInput();
            }

            // Determine account information based on registration type
            if ($request->registration_type === 'parent') {
                // Parent registration: Create account with parent's info
                $accountName = $request->parent_first_name . ' ' . $request->parent_last_name;
                $accountEmail = $request->parent_email;
                $accountPhone = $request->parent_phone;
                $accountFirstName = $request->parent_first_name;
                $accountLastName = $request->parent_last_name;
            } else {
                // Self registration: Create account with member's info
                $accountName = $request->first_name . ' ' . $request->last_name;
                $accountEmail = $request->email;
                $accountPhone = $request->phone;
                $accountFirstName = $request->first_name;
                $accountLastName = $request->last_name;
            }

            // Create User account
            $user = new User();
            $user->name = $accountName;
            $user->email = $accountEmail;
            $user->phone_number = $accountPhone;
            $user->password = Hash::make($request->password);
            $user->type = $userRole->name;
            $user->profile = 'avatar.png';
            $user->lang = 'english';
            $user->parent_id = 2; // Assigned to owner with ID 2
            $user->email_verified_at = now();
            $user->save();
            $user->assignRole($userRole);

            // Create Member record (parent account or self account)
            $member = new Member();
            $member->member_id = $this->generateMemberNumber();
            $member->user_id = $user->id;
            $member->first_name = $accountFirstName;
            $member->last_name = $accountLastName;
            $member->password = Hash::make($request->password);
            $member->email = $accountEmail;
            $member->phone = $accountPhone;
            $member->parent_id = 2; // Assigned to owner with ID 2
            $member->relationship = $request->registration_type;

            if ($request->registration_type === 'self') {
                // Self registration: store member's own information
                $member->dob = $request->dob;
                $member->address = $request->address;
                $member->gender = $request->gender;
                $member->emergency_contact_information = $request->emergency_contact_information;
                $member->notes = $request->notes;
                $member->membership_part = !empty($request->plan_id) ? 'on' : 'off';
                $member->is_parent = false;

                // Handle image upload
                if ($request->hasFile('image')) {
                    $extension = $request->file('image')->getClientOriginalExtension();
                    $name = \Str::uuid() . '.' . $extension;
                    $request->file('image')->storeAs('upload/member/', $name);
                    $member->image = $name;
                }
            } else {
                // Parent registration: Mark as parent account
                $member->is_parent = true;
                $member->dob = null;
                $member->address = $request->address ?? '';
                $member->gender = null;
                $member->membership_part = 'off'; // Parents don't have their own membership
            }

            $member->save();

            // If parent registration, create child record
            if ($request->registration_type === 'parent') {
                $child = new Member();
                $child->member_id = $this->generateMemberNumber();
                $child->user_id = $user->id; // Child shares parent's user account for authentication
                $child->first_name = $request->first_name;
                $child->last_name = $request->last_name;
                $child->email = $request->email;
                $child->phone = $request->phone;
                $child->dob = $request->dob;
                $child->address = $request->address;
                $child->gender = $request->gender;
                $child->emergency_contact_information = $request->emergency_contact_information;
                $child->notes = $request->notes;
                $child->membership_part = !empty($request->plan_id) ? 'on' : 'off';
                $child->parent_id = 2;
                $child->parent_member_id = $member->id; // Link to parent member
                $child->is_parent = false;
                $child->relationship = 'child';

                // Handle image upload for child
                if ($request->hasFile('image')) {
                    $extension = $request->file('image')->getClientOriginalExtension();
                    $name = \Str::uuid() . '.' . $extension;
                    $request->file('image')->storeAs('upload/member/', $name);
                    $child->image = $name;
                }

                $child->save();

                // Assign membership to child, not parent
                $memberForMembership = $child;
            } else {
                // Self registration: membership goes to the member
                $memberForMembership = $member;
            }

            // Create membership if plan_id is provided
            if (!empty($request->plan_id)) {
                $plan = MembershipPlan::find($request->plan_id);
                
                if ($plan) {
                    // Calculate expiry date based on plan duration
                    $expiryDate = $this->calculateExpiryDate($plan->duration);
                    
                    $membership = new Membership();
                    $membership->member_id = $memberForMembership->id;
                    $membership->plan_id = $request->plan_id;
                    $membership->start_date = now()->format('Y-m-d');
                    $membership->expiry_date = $expiryDate;
                    $membership->status = 'Pending'; // Set to Pending until payment is confirmed
                    $membership->parent_id = 2;
                    $membership->save();
                }
            }

            // Send notification email if configured
            $this->sendRegistrationNotification($member);

            // Return member ID (for parent, this is the parent member who will login)
            // If payment is required (plan selected), return member ID for payment
            if (!empty($request->plan_id) && $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Registration successful! Processing payment...'),
                    'member_id' => $memberForMembership->id // Use child ID for membership payment
                ]);
            }

            // No payment needed or non-AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Registration successful!'),
                    'redirect' => route('public.register.success')
                ]);
            }

            return redirect()->route('public.register.success')
                ->with('success', __('Registration successful! You can now log in with your credentials.'));

        } catch (\Exception $e) {
            \Log::error('Public member registration error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => __('An error occurred during registration. Please try again.')], 500);
            }
            
            return redirect()->back()
                ->with('error', __('An error occurred during registration. Please try again.'))
                ->withInput();
        }
    }

    /**
     * Show registration success page
     */
    public function success()
    {
        return view('public.register-success');
    }

    /**
     * Generate unique member number for parent_id = 2
     */
    private function generateMemberNumber()
    {
        $latestMember = Member::where('parent_id', 2)
            ->orderBy('member_id', 'desc')
            ->first();

        return $latestMember ? $latestMember->member_id + 1 : 1;
    }

    /**
     * Calculate membership expiry date based on duration
     */
    private function calculateExpiryDate($duration)
    {
        switch ($duration) {
            case 'Day Pass':
                return \Carbon\Carbon::now()->addDays(1)->format('Y-m-d');
            case 'Monthly':
                return \Carbon\Carbon::now()->addMonths(1)->format('Y-m-d');
            case '3-Month':
                return \Carbon\Carbon::now()->addMonths(3)->format('Y-m-d');
            case '6-Month':
                return \Carbon\Carbon::now()->addMonths(6)->format('Y-m-d');
            case 'Yearly':
                return \Carbon\Carbon::now()->addYears(1)->format('Y-m-d');
            default:
                return \Carbon\Carbon::now()->addMonths(1)->format('Y-m-d');
        }
    }

    /**
     * Send registration notification email
     */
    private function sendRegistrationNotification($member)
    {
        try {
            $module = 'member_create';
            $notification = Notification::where('parent_id', 2)
                ->where('module', $module)
                ->first();

            if (!empty($notification) && !empty($member->email)) {
                $setting = settingsById(2);
                
                if (!empty($notification->enabled_email) && $notification->enabled_email == 1) {
                    $notificationResponse = MessageReplace($notification, $member->id);
                    
                    $data = [
                        'subject' => $notificationResponse['subject'],
                        'message' => $notificationResponse['message'],
                        'module' => $module,
                        'logo' => $setting['company_logo'] ?? 'logo.png',
                    ];
                    
                    commonEmailSend($member->email, $data);
                }

                // Send SMS if enabled
                if (!empty($notification->enabled_sms) && $notification->enabled_sms == 1) {
                    $twilio_sid = getSettingsValByName('twilio_sid');
                    if (!empty($twilio_sid) && !empty($member->phone)) {
                        $smsMessage = $notificationResponse['sms_message'] ?? 'Welcome! Your member account has been created successfully.';
                        send_twilio_msg($member->phone, $smsMessage);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Public registration notification error: ' . $e->getMessage());
            // Don't fail registration if notification fails
        }
    }

    /**
     * Process Stripe payment for membership plan
     */
    public function processPayment(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'member_id' => 'required|exists:members,id',
                'plan_id' => 'required|exists:membership_plans,id',
                'stripeToken' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $member = Member::findOrFail($request->member_id);
            $plan = MembershipPlan::findOrFail($request->plan_id);
            $settings = invoicePaymentSettings(2); // Get settings for owner ID 2

            if ($settings['STRIPE_PAYMENT'] != 'on' || empty($settings['STRIPE_SECRET'])) {
                return response()->json(['error' => 'Stripe payment is not enabled'], 400);
            }

            // Process Stripe payment
            \Stripe\Stripe::setApiKey($settings['STRIPE_SECRET']);
            $transactionID = uniqid('PUBLIC_REG_', true);

            $charge = \Stripe\Charge::create([
                "amount" => 100 * $plan->price, // Convert to cents
                "currency" => $settings['CURRENCY'] ?? 'usd',
                "source" => $request->stripeToken,
                "description" => "Membership Plan Registration - " . $plan->plan_name,
                "metadata" => [
                    "order_id" => $transactionID,
                    "member_id" => $member->id,
                    "plan_id" => $plan->id
                ],
            ]);

            if ($charge['paid'] && $charge['status'] == 'succeeded') {
                // Create payment record
                $payment = new MembershipPayment();
                $payment->payment_id = $this->generatePaymentNumber();
                $payment->member_id = $member->id;
                $payment->plan_id = $plan->id;
                $payment->transaction_id = $transactionID;
                $payment->payment_method = 'Online Payment';
                $payment->amount = $plan->price;
                $payment->status = 'succeeded';
                $payment->payment_date = now()->format('Y-m-d');
                $payment->parent_id = 2;
                $payment->save();

                // Activate membership
                $membership = Membership::where('member_id', $member->id)
                    ->where('plan_id', $plan->id)
                    ->latest()
                    ->first();

                if ($membership) {
                    $membership->status = 'Active';
                    $membership->save();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful! Your membership is now active.',
                    'redirect' => route('public.register.success')
                ]);
            }

            return response()->json(['error' => 'Payment failed. Please try again.'], 400);

        } catch (\Exception $e) {
            \Log::error('Public registration payment error: ' . $e->getMessage());
            return response()->json(['error' => 'Payment processing failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate payment number
     */
    private function generatePaymentNumber()
    {
        $latestPayment = MembershipPayment::where('parent_id', 2)
            ->orderBy('payment_id', 'desc')
            ->first();

        return $latestPayment ? $latestPayment->payment_id + 1 : 1;
    }
}
