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
     * Step 1: Show parent/guardian information form
     */
    public function showStep1()
    {
        // Only clear session data if not coming from a successful registration
        // This prevents clearing data when users refresh or navigate after completing registration
        if (!session('registration_just_completed')) {
            session()->forget(['registration_step1', 'registration_step2', 'registration_step3', 'registration_data', 'registration_form_data']);
        } else {
            // Clear the completion flag but keep showing step 1
            session()->forget('registration_just_completed');
        }
        
        return view('public.register-step1');
    }

    /**
     * Step 1: Process parent/guardian information
     */
    public function processStep1(Request $request)
    {
        $rules = [
            'parent_first_name' => 'required|string|max:255',
            'parent_last_name' => 'required|string|max:255',
            'parent_email' => 'required|email|unique:users,email',
            'parent_phone' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ];

        $messages = [
            'parent_first_name.required' => __('Parent/Guardian first name is required.'),
            'parent_last_name.required' => __('Parent/Guardian last name is required.'),
            'parent_email.required' => __('Parent/Guardian email is required.'),
            'parent_email.unique' => __('This email is already registered.'),
            'parent_phone.required' => __('Parent/Guardian phone is required.'),
            'password.required' => __('Password is required.'),
            'password.confirmed' => __('Password confirmation does not match.'),
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Store step 1 data in session
        session([
            'registration_step1' => [
                'parent_first_name' => $request->parent_first_name,
                'parent_last_name' => $request->parent_last_name,
                'parent_email' => $request->parent_email,
                'parent_phone' => $request->parent_phone,
                'password' => $request->password,
            ]
        ]);

        return redirect()->route('public.register.step2');
    }

    /**
     * Step 2: Show children information form
     */
    public function showStep2()
    {
        if (!session('registration_step1')) {
            return redirect()->route('public.register')
                ->with('error', __('Please complete step 1 first.'));
        }

        $membershipPlans = MembershipPlan::where('parent_id', 2)->get();
        $step1Data = session('registration_step1');
        $step2Data = session('registration_step2', []);
        
        return view('public.register-step2', compact('membershipPlans', 'step1Data', 'step2Data'));
    }

    /**
     * Step 2: Process children information
     */
    public function processStep2(Request $request)
    {
        if (!session('registration_step1')) {
            return redirect()->route('public.register')
                ->with('error', __('Please complete step 1 first.'));
        }

        $rules = [
            'children' => 'required|array|min:1',
            'children.*.first_name' => 'required|string|max:255',
            'children.*.last_name' => 'required|string|max:255',
            'children.*.email' => 'required|email|unique:users,email',
            'children.*.dob' => 'required|date',
            'children.*.gender' => 'required|in:Male,Female',
            'children.*.plan_id' => 'nullable|exists:membership_plans,id',
        ];

        $messages = [
            'children.required' => __('You must add at least one child.'),
            'children.*.first_name.required' => __('Child first name is required.'),
            'children.*.last_name.required' => __('Child last name is required.'),
            'children.*.email.required' => __('Child email is required.'),
            'children.*.email.email' => __('Child email must be a valid email address.'),
            'children.*.email.unique' => __('This email is already registered.'),
            'children.*.dob.required' => __('Child date of birth is required.'),
            'children.*.gender.required' => __('Child gender is required.'),
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle file uploads and prepare children data
        $childrenData = [];
        foreach ($request->children as $key => $childData) {
            $imageName = null;
            if ($request->hasFile("children.{$key}.image")) {
                $file = $request->file("children.{$key}.image");
                $extension = $file->getClientOriginalExtension();
                $imageName = \Str::uuid() . '.' . $extension;
                $file->storeAs('upload/member/', $imageName);
            }

            $childrenData[$key] = [
                'first_name' => $childData['first_name'] ?? '',
                'last_name' => $childData['last_name'] ?? '',
                'email' => $childData['email'] ?? '',
                'phone' => $childData['phone'] ?? '',
                'dob' => $childData['dob'] ?? '',
                'gender' => $childData['gender'] ?? '',
                'address' => $childData['address'] ?? '',
                'emergency_contact' => $childData['emergency_contact'] ?? '',
                'plan_id' => $childData['plan_id'] ?? null,
                'image' => $imageName,
            ];
        }

        // Store step 2 data in session
        session([
            'registration_step2' => [
                'children' => $childrenData,
            ]
        ]);

        return redirect()->route('public.register.step3');
    }

    /**
     * Step 3: Show waiver acceptance form
     */
    public function showStep3()
    {
        if (!session('registration_step1') || !session('registration_step2')) {
            return redirect()->route('public.register')
                ->with('error', __('Please complete all previous steps first.'));
        }

        $step1Data = session('registration_step1');
        $step2Data = session('registration_step2');
        
        return view('public.register-step3', compact('step1Data', 'step2Data'));
    }

    /**
     * Step 3: Process waiver acceptance and create accounts
     */
    public function processStep3(Request $request)
    {
        if (!session('registration_step1') || !session('registration_step2')) {
            return redirect()->route('public.register')
                ->with('error', __('Please complete all previous steps first.'));
        }

        $rules = [
            'waiver_accepted' => 'required|accepted',
        ];

        $messages = [
            'waiver_accepted.required' => __('You must accept the waiver to continue.'),
            'waiver_accepted.accepted' => __('You must accept the waiver to continue.'),
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Store waiver acceptance
        session([
            'registration_step3' => [
                'waiver_accepted' => true,
                'waiver_accepted_at' => now()->toDateTimeString(),
            ]
        ]);

        try {
            $step1Data = session('registration_step1');
            $step2Data = session('registration_step2');

            // Get the member role
            $userRole = Role::where('name', 'member')
                ->where('parent_id', 2)
                ->first();

            if (!$userRole) {
                return redirect()->back()
                    ->with('error', __('Member role not found. Please contact the administrator.'));
            }

            // Create User account for parent
            $user = new User();
            $user->name = $step1Data['parent_first_name'] . ' ' . $step1Data['parent_last_name'];
            $user->email = $step1Data['parent_email'];
            $user->phone_number = $step1Data['parent_phone'];
            $user->password = Hash::make($step1Data['password']);
            $user->type = $userRole->name;
            $user->profile = 'avatar.png';
            $user->lang = 'english';
            $user->parent_id = 2;
            $user->email_verified_at = now();
            $user->save();
            $user->assignRole($userRole);

            // Create Member record for parent
            $member = new Member();
            $member->member_id = $this->generateMemberNumber();
            $member->user_id = $user->id;
            $member->first_name = $step1Data['parent_first_name'];
            $member->last_name = $step1Data['parent_last_name'];
            $member->email = $step1Data['parent_email'];
            $member->phone = $step1Data['parent_phone'];
            $member->notes = '';
            $member->membership_part = 'off';
            $member->parent_id = 2;
            $member->is_parent = true;
            $member->relationship = 'parent';
            $member->dob = null;
            $member->address = '';
            $member->gender = null;
            $member->save();

            // Check if any child has a plan (requires payment)
            $hasPlans = false;
            foreach ($step2Data['children'] as $childData) {
                if (!empty($childData['plan_id'])) {
                    $hasPlans = true;
                    break;
                }
            }

            if ($hasPlans) {
                // Store registration data for payment processing
                session([
                    'registration_data' => [
                        'user_id' => $user->id,
                        'parent_member_id' => $member->id,
                        'parent_password' => $step1Data['password'],
                        'children' => $step2Data['children'],
                        'registration_type' => 'parent',
                        'waiver_accepted' => true,
                    ]
                ]);

                return redirect()->route('public.register.payment.summary');
            }

            // No payment needed - create children immediately
            foreach ($step2Data['children'] as $key => $childData) {
                $child = new Member();
                $child->member_id = $this->generateMemberNumber();
                $child->user_id = null;
                $child->first_name = $childData['first_name'];
                $child->last_name = $childData['last_name'];
                $child->email = $childData['email'] ?? null;
                $child->phone = $childData['phone'] ?? null;
                $child->dob = $childData['dob'];
                $child->address = $childData['address'] ?? '';
                $child->gender = $childData['gender'];
                $child->emergency_contact_information = $childData['emergency_contact'] ?? '';
                $child->notes = '';
                $child->membership_part = 'off';
                $child->parent_id = 2;
                $child->parent_member_id = $member->id;
                $child->is_parent = false;
                $child->relationship = 'child';

                if (!empty($childData['image'])) {
                    $child->image = $childData['image'];
                }

                $child->save();
            }

            // Send notification
            $this->sendRegistrationNotification($member);

            // Clear session data
            session()->forget(['registration_step1', 'registration_step2', 'registration_step3']);
            
            // Mark that registration was just completed to prevent session clearing on success page
            session(['registration_just_completed' => true]);

            return redirect()->route('public.register.success')
                ->with('success', __('Registration successful! You can now log in with your credentials.'));

        } catch (\Exception $e) {
            \Log::error('Public member registration error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', __('An error occurred during registration. Please try again.'));
        }
    }

    /**
     * Check if email already exists (AJAX endpoint)
     */
    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        
        if (empty($email)) {
            return response()->json(['exists' => false]);
        }
        
        $exists = User::where('email', $email)->exists();
        
        return response()->json(['exists' => $exists]);
    }

    /**
     * Show registration success page
     */
    public function success()
    {
        return view('public.register-success');
    }
    public function showPaymentSummary()
    {
        $registrationData = session('registration_data');
        
        if (!$registrationData) {
            return redirect()->route('public.register')
                ->with('error', __('No registration data found. Please register again.'));
        }
        
        $totalAmount = 0;
        $items = [];
        
        if ($registrationData['registration_type'] === 'parent') {
            // Parent registration - calculate total for all children
            foreach ($registrationData['children'] as $childData) {
                if (!empty($childData['plan_id'])) {
                    $plan = MembershipPlan::find($childData['plan_id']);
                    if ($plan) {
                        $items[] = [
                            'member_name' => $childData['first_name'] . ' ' . $childData['last_name'],
                            'plan_name' => $plan->plan_name,
                            'duration' => $plan->duration,
                            'billing_frequency' => $plan->billing_frequency,
                            'amount' => $plan->price,
                            'expiry_date' => $this->calculateExpiryDate($plan->duration)
                        ];
                        $totalAmount += $plan->price;
                    }
                }
            }
            
            $member = Member::find($registrationData['parent_member_id']);
        } else {
            // Self registration
            $plan = MembershipPlan::find($registrationData['plan_id']);
            $member = Member::find($registrationData['member_id']);
            
            if ($plan && $member) {
                $items[] = [
                    'member_name' => $member->first_name . ' ' . $member->last_name,
                    'plan_name' => $plan->plan_name,
                    'duration' => $plan->duration,
                    'billing_frequency' => $plan->billing_frequency,
                    'amount' => $plan->price,
                    'expiry_date' => $this->calculateExpiryDate($plan->duration)
                ];
                $totalAmount = $plan->price;
            }
        }
        
        $invoicePaymentSettings = invoicePaymentSettings(2);
        $settings = settings();
        
        return view('public.payment-summary', compact('items', 'totalAmount', 'member', 'registrationData', 'invoicePaymentSettings', 'settings'));
    }

    /**
     * Process payment and complete registration
     */
    public function processPayment(Request $request)
    {
        $registrationData = session('registration_data');
        
        if (!$registrationData) {
            return redirect()->route('public.register')
                ->with('error', __('No registration data found. Please register again.'));
        }
        
        try {
            $paymentStatus = 'Pending';
            $membershipStatus = 'Pending';
            $transactionId = null;
            
            // Process Stripe payment if selected
            if ($request->payment_method === 'stripe' && $request->has('stripe_token')) {
                $settings = invoicePaymentSettings(2);
                
                if ($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_SECRET'])) {
                    \Stripe\Stripe::setApiKey($settings['STRIPE_SECRET']);
                    
                    // Calculate total amount
                    $totalAmount = 0;
                    if ($registrationData['registration_type'] === 'parent') {
                        foreach ($registrationData['children'] as $childData) {
                            if (!empty($childData['plan_id'])) {
                                $plan = MembershipPlan::find($childData['plan_id']);
                                if ($plan) {
                                    $totalAmount += $plan->price;
                                }
                            }
                        }
                    } else {
                        $plan = MembershipPlan::find($registrationData['plan_id']);
                        if ($plan) {
                            $totalAmount = $plan->price;
                        }
                    }
                    
                    try {
                        $charge = \Stripe\Charge::create([
                            'amount' => $totalAmount * 100, // Convert to cents
                            'currency' => $settings['CURRENCY'] ?? 'usd',
                            'source' => $request->stripe_token,
                            'description' => 'Membership Registration Payment',
                        ]);
                        
                        if ($charge->status === 'succeeded') {
                            $paymentStatus = 'Paid';
                            $membershipStatus = 'Active';
                            $transactionId = $charge->id;
                        }
                    } catch (\Exception $e) {
                        \Log::error('Stripe payment error: ' . $e->getMessage());
                        return redirect()->back()
                            ->with('error', __('Payment failed: ') . $e->getMessage());
                    }
                }
            } elseif ($request->payment_method === 'bank_transfer') {
                $paymentStatus = 'Pending';
                $membershipStatus = 'Pending';
            }
            
            // Create children and memberships after successful payment
            if ($registrationData['registration_type'] === 'parent') {
                $parentMember = Member::find($registrationData['parent_member_id']);
                $childrenCredentials = []; // Store credentials for email
                $parentPassword = $registrationData['parent_password']; // Use parent's password for all children
                
                foreach ($registrationData['children'] as $childData) {
                    // Create user account for child member
                    $childEmail = $childData['email']; // Use email from registration form
                    
                    $childUser = new User();
                    $childUser->name = $childData['first_name'] . ' ' . $childData['last_name'];
                    $childUser->email = $childEmail;
                    $childUser->password = Hash::make($parentPassword); // Use parent's password
                    $childUser->type = 'member';
                    $childUser->lang = 'english';
                    $childUser->parent_id = 2;
                    $childUser->is_active = 1;
                    $childUser->email_verified_at = now();
                    $childUser->save();
                    
                    // Assign member role
                    $memberRole = Role::where('name', 'member')->where('parent_id', 2)->first();
                    if ($memberRole) {
                        $childUser->assignRole($memberRole);
                    }
                    
                    // Create member record
                    $child = new Member();
                    $child->member_id = $this->generateMemberNumber();
                    $child->user_id = $childUser->id;
                    $child->first_name = $childData['first_name'];
                    $child->last_name = $childData['last_name'];
                    $child->email = $childEmail;
                    $child->phone = $childData['phone'] ?? null;
                    $child->dob = $childData['dob'];
                    $child->address = $childData['address'] ?? '';
                    $child->gender = $childData['gender'];
                    $child->emergency_contact_information = $childData['emergency_contact'] ?? '';
                    $child->notes = '';
                    $child->membership_part = !empty($childData['plan_id']) ? 'on' : 'off';
                    $child->parent_id = 2;
                    $child->parent_member_id = $parentMember->id;
                    $child->is_parent = 0;
                    $child->relationship = 'child';
                    
                    // Use pre-uploaded image filename if available
                    if (!empty($childData['image'])) {
                        $child->image = $childData['image'];
                    }
                    
                    $child->save();
                    
                    // Store credentials for parent notification
                    $childrenCredentials[] = [
                        'name' => $child->first_name . ' ' . $child->last_name,
                        'email' => $childEmail,
                    ];
                    
                    // Create membership if plan selected
                    if (!empty($childData['plan_id'])) {
                        $plan = MembershipPlan::find($childData['plan_id']);
                        
                        if ($plan) {
                            $membership = new Membership();
                            $membership->member_id = $child->id;
                            $membership->plan_id = $childData['plan_id'];
                            $membership->start_date = now()->format('Y-m-d');
                            $membership->expiry_date = $this->calculateExpiryDate($plan->duration);
                            $membership->status = $membershipStatus;
                            $membership->parent_id = 2;
                            $membership->save();
                            
                            // Create payment record
                            $payment = new MembershipPayment();
                            $payment->payment_id = $this->generatePaymentNumber();
                            $payment->member_id = $child->id;
                            $payment->plan_id = $childData['plan_id'];
                            $payment->amount = $plan->price;
                            $payment->payment_method = $request->payment_method ?? 'bank_transfer';
                            $payment->status = $paymentStatus;
                            $payment->payment_date = now();
                            $payment->transaction_id = $transactionId;
                            $payment->parent_id = 2;
                            $payment->save();
                        }
                    }
                }
                
                // Send notification to parent with children's credentials
                $this->sendParentNotificationWithCredentials($parentMember, $childrenCredentials);
            } else {
                // Self registration
                $member = Member::find($registrationData['member_id']);
                $plan = MembershipPlan::find($registrationData['plan_id']);
                
                if ($member && $plan) {
                    $membership = new Membership();
                    $membership->member_id = $member->id;
                    $membership->plan_id = $plan->plan_id;
                    $membership->start_date = now()->format('Y-m-d');
                    $membership->expiry_date = $this->calculateExpiryDate($plan->duration);
                    $membership->status = $membershipStatus;
                    $membership->parent_id = 2;
                    $membership->save();
                    
                    // Create payment record
                    $payment = new MembershipPayment();
                    $payment->payment_id = $this->generatePaymentNumber();
                    $payment->member_id = $member->id;
                    $payment->plan_id = $plan->plan_id;
                    $payment->amount = $plan->price;
                    $payment->payment_method = $request->payment_method ?? 'bank_transfer';
                    $payment->status = $paymentStatus;
                    $payment->payment_date = now();
                    $payment->transaction_id = $transactionId;
                    $payment->parent_id = 2;
                    $payment->save();
                    
                    $this->sendRegistrationNotification($member);
                }
            }
            
            // Clear session data
            session()->forget('registration_data');
            session()->forget('registration_form_data');
            
            return redirect()->route('public.register.success')
                ->with('success', __('Registration and payment completed successfully!'));
                
        } catch (\Exception $e) {
            \Log::error('Payment processing error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', __('An error occurred while processing payment. Please try again.'));
        }
    }

    /**
     * Generate unique payment number
     */
    private function generatePaymentNumber()
    {
        $latestPayment = MembershipPayment::where('parent_id', 2)
            ->orderBy('payment_id', 'desc')
            ->first();
        
        return $latestPayment ? $latestPayment->payment_id + 1 : 1;
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
     * Generate unique email for child if not provided
     */
    private function generateChildEmail($firstName, $lastName)
    {
        $baseEmail = strtolower($firstName . '.' . $lastName);
        $baseEmail = preg_replace('/[^a-z0-9.]/', '', $baseEmail);
        
        $counter = 1;
        $email = $baseEmail . '@member.local';
        
        // Check if email exists and increment until we find a unique one
        while (User::where('email', $email)->exists()) {
            $email = $baseEmail . $counter . '@member.local';
            $counter++;
        }
        
        return $email;
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
     * Send parent notification with children's login credentials
     */
    private function sendParentNotificationWithCredentials($parentMember, $childrenCredentials)
    {
        try {
            $setting = settingsById(2);
            
            // Build credentials message
            $credentialsHtml = '<div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #007bff;">';
            $credentialsHtml .= '<h3 style="color: #007bff; margin-top: 0;">Child Member Login Credentials</h3>';
            $credentialsHtml .= '<p style="margin-bottom: 15px;">Your children can use these credentials to check in at events:</p>';
            $credentialsHtml .= '<p style="margin-bottom: 10px;"><strong>All children use the same password as your parent account.</strong></p>';
            
            foreach ($childrenCredentials as $credential) {
                $credentialsHtml .= '<div style="margin-bottom: 15px; padding: 10px; background-color: white; border-radius: 5px;">';
                $credentialsHtml .= '<strong style="color: #333;">Name:</strong> ' . htmlspecialchars($credential['name']) . '<br>';
                $credentialsHtml .= '<strong style="color: #333;">Email/Username:</strong> ' . htmlspecialchars($credential['email']);
                $credentialsHtml .= '</div>';
            }
            
            $credentialsHtml .= '<p style="margin-top: 15px; color: #666; font-size: 14px;"><em>Please save these credentials securely. Children will need them to check in at events.</em></p>';
            $credentialsHtml .= '</div>';
            
            $message = '<p>Dear ' . htmlspecialchars($parentMember->first_name) . ',</p>';
            $message .= '<p>Thank you for registering your children! Registration has been completed successfully.</p>';
            $message .= $credentialsHtml;
            $message .= '<p style="margin-top: 20px;">Login URL: <a href="' . env('APP_URL') . '/login">' . env('APP_URL') . '/login</a></p>';
            $message .= '<p>If you have any questions, please don\'t hesitate to contact us.</p>';
            $message .= '<p>Best regards,<br>' . ($setting['app_name'] ?? 'Membership Portal') . '</p>';
            
            $data = [
                'subject' => 'Child Member Registration - Login Credentials',
                'message' => $message,
                'module' => 'child_registration',
                'logo' => $setting['company_logo'] ?? 'logo.png',
            ];
            
            commonEmailSend($parentMember->email, $data);
            
        } catch (\Exception $e) {
            \Log::error('Parent notification error: ' . $e->getMessage());
            // Don't fail registration if notification fails
        }
    }
}
