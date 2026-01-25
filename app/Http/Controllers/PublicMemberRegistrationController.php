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
        
        // Get registration form data from session if user is returning from payment page
        $formData = session('registration_form_data', []);
        
        return view('public.register', compact('membershipPlans', 'formData'));
    }

    /**
     * Handle the public registration submission
     */
    public function store(Request $request)
    {
        // Build validation rules conditionally based on registration type
        $rules = [
            'registration_type' => 'required|in:self,parent',
            'password' => 'required|string|min:6|confirmed',
        ];

        $messages = [
            'registration_type.required' => __('Please select whether you are registering yourself or a child.'),
        ];

        // Add conditional validation based on registration type
        if ($request->registration_type === 'self') {
            $rules['age_confirmation'] = 'required|accepted';
            $rules['first_name'] = 'required|string|max:255';
            $rules['last_name'] = 'required|string|max:255';
            $rules['email'] = 'required|email|unique:users,email';
            $rules['phone'] = 'required|string';
            $rules['dob'] = 'required|date';
            $rules['address'] = 'required|string';
            $rules['gender'] = 'required|in:Male,Female';
            $rules['plan_id'] = 'nullable|exists:membership_plans,id';
            $messages['age_confirmation.required'] = __('You must confirm that you are 18 years or older to register yourself.');
            $messages['age_confirmation.accepted'] = __('You must confirm that you are 18 years or older to register yourself.');
        } elseif ($request->registration_type === 'parent') {
            $rules['parent_first_name'] = 'required|string|max:255';
            $rules['parent_last_name'] = 'required|string|max:255';
            $rules['parent_email'] = 'required|email|unique:users,email';
            $rules['parent_phone'] = 'required|string';
            $rules['children'] = 'required|array|min:1';
            $rules['children.*.first_name'] = 'required|string|max:255';
            $rules['children.*.last_name'] = 'required|string|max:255';
            $rules['children.*.email'] = 'required|email|unique:users,email';
            $rules['children.*.dob'] = 'required|date';
            $rules['children.*.gender'] = 'required|in:Male,Female';
            $rules['children.*.plan_id'] = 'nullable|exists:membership_plans,id';
            $messages['parent_first_name.required'] = __('Parent/Guardian first name is required when registering a child.');
            $messages['parent_last_name.required'] = __('Parent/Guardian last name is required when registering a child.');
            $messages['parent_email.required'] = __('Parent/Guardian email is required when registering a child.');
            $messages['parent_phone.required'] = __('Parent/Guardian phone is required when registering a child.');
            $messages['children.required'] = __('You must add at least one child.');
            $messages['children.*.first_name.required'] = __('Child first name is required.');
            $messages['children.*.last_name.required'] = __('Child last name is required.');
            $messages['children.*.email.required'] = __('Child email is required.');
            $messages['children.*.email.email'] = __('Child email must be a valid email address.');
            $messages['children.*.email.unique'] = __('This email is already registered.');
            $messages['children.*.dob.required'] = __('Child date of birth is required.');
            $messages['children.*.gender.required'] = __('Child gender is required.');
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
                $member->is_parent = 0;
                $member->relationship = 'self';

                // Handle image upload
                if ($request->hasFile('image')) {
                    $extension = $request->file('image')->getClientOriginalExtension();
                    $name = \Str::uuid() . '.' . $extension;
                    $request->file('image')->storeAs('upload/member/', $name);
                    $member->image = $name;
                }
            } else {
                // Parent registration: Mark as parent account
                $member->is_parent = 1;
                $member->relationship = 'parent';
                $member->dob = null;
                $member->address = $request->address ?? '';
                $member->gender = null;
                $member->membership_part = 'off'; // Parents don't have their own membership
            }

            $member->save();

            // If parent registration with children, check if payment is needed
            if ($request->registration_type === 'parent' && $request->has('children')) {
                $hasPlans = false;
                foreach ($request->children as $childData) {
                    if (!empty($childData['plan_id'])) {
                        $hasPlans = true;
                        break;
                    }
                }
                
                // If any child has a plan, redirect to payment summary
                if ($hasPlans) {
                    // Store registration data in session (including form data for back button)
                    session([
                        'registration_data' => [
                            'user_id' => $user->id,
                            'parent_member_id' => $member->id,
                            'parent_password' => $request->password,
                            'children' => $request->children,
                            'registration_type' => 'parent'
                        ],
                        'registration_form_data' => [
                            'parent_first_name' => $request->parent_first_name,
                            'parent_last_name' => $request->parent_last_name,
                            'parent_email' => $request->parent_email,
                            'parent_phone' => $request->parent_phone,
                            'children' => $request->children,
                            'registration_type' => 'parent'
                        ]
                    ]);
                    
                    return redirect()->route('public.register.payment.summary');
                }
                
                // No plans selected - create children immediately without payment
                $children = $request->children;
                $childrenIds = [];
                
                foreach ($children as $childData) {
                    $child = new Member();
                    $child->member_id = $this->generateMemberNumber();
                    $child->user_id = null; // Children do NOT have their own user account - cannot log in
                    $child->first_name = $childData['first_name'];
                    $child->last_name = $childData['last_name'];
                    $child->email = $childData['email'] ?? null;
                    $child->phone = $childData['phone'] ?? null;
                    $child->dob = $childData['dob'];
                    $child->address = $childData['address'] ?? '';
                    $child->gender = $childData['gender'];
                    $child->emergency_contact_information = $childData['emergency_contact'] ?? '';
                    $child->notes = '';
                    $child->membership_part = !empty($childData['plan_id']) ? 'on' : 'off';
                    $child->parent_id = 2;
                    $child->parent_member_id = $member->id; // Link to parent member
                    $child->is_parent = false;
                    $child->relationship = 'child';

                    // Handle image upload for child
                    if ($request->hasFile("children.{$childData['first_name']}.image")) {
                        $extension = $request->file("children.{$childData['first_name']}.image")->getClientOriginalExtension();
                        $name = \Str::uuid() . '.' . $extension;
                        $request->file("children.{$childData['first_name']}.image")->storeAs('upload/member/', $name);
                        $child->image = $name;
                    }

                    $child->save();
                    $childrenIds[] = $child->id;
                    
                    // Don't create memberships here - only create child records
                    // Memberships will be created during payment processing
                }
            } else {
                // Self registration: membership goes to the member
                $memberForMembership = $member;
                
                // Create membership if plan_id is provided
                if (!empty($request->plan_id)) {
                    $plan = MembershipPlan::find($request->plan_id);
                    
                    if ($plan) {
                        // Store registration data and redirect to payment
                        session([
                            'registration_data' => [
                                'user_id' => $user->id,
                                'member_id' => $member->id,
                                'plan_id' => $request->plan_id,
                                'registration_type' => 'self'
                            ],
                            'registration_form_data' => [
                                'registration_type' => 'self',
                                'first_name' => $request->first_name,
                                'last_name' => $request->last_name,
                                'email' => $request->email,
                                'phone' => $request->phone,
                                'dob' => $request->dob,
                                'gender' => $request->gender,
                                'address' => $request->address,
                                'plan_id' => $request->plan_id,
                            ]
                        ]);
                        
                        return redirect()->route('public.register.payment.summary');
                    }
                }
            }

            // Send notification email if configured
            $this->sendRegistrationNotification($member);

            // Clear session data before redirecting to success
            session()->forget('registration_data');
            session()->forget('registration_form_data');

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
     * Show payment summary page
     */
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
