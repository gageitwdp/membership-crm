<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipPlan;
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
        $validator = \Validator::make(
            $request->all(),
            [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
                'phone' => 'required|string',
                'dob' => 'required|date',
                'address' => 'required|string',
                'gender' => 'required|in:Male,Female',
                'plan_id' => 'nullable|exists:membership_plans,id',
            ]
        );

        if ($validator->fails()) {
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
                return redirect()->back()
                    ->with('error', __('Member role not found. Please contact the administrator.'))
                    ->withInput();
            }

            // Create User account
            $user = new User();
            $user->name = $request->first_name . ' ' . $request->last_name;
            $user->email = $request->email;
            $user->phone_number = $request->phone;
            $user->password = Hash::make($request->password);
            $user->type = $userRole->name;
            $user->profile = 'avatar.png';
            $user->lang = 'english';
            $user->parent_id = 2; // Assigned to owner with ID 2
            $user->email_verified_at = now();
            $user->save();
            $user->assignRole($userRole);

            // Create Member record
            $member = new Member();
            $member->member_id = $this->generateMemberNumber();
            $member->user_id = $user->id;
            $member->first_name = $request->first_name;
            $member->last_name = $request->last_name;
            $member->password = Hash::make($request->password);
            $member->email = $request->email;
            $member->phone = $request->phone;
            $member->dob = $request->dob;
            $member->address = $request->address;
            $member->gender = $request->gender;
            $member->emergency_contact_information = $request->emergency_contact_information;
            $member->notes = $request->notes;
            $member->membership_part = !empty($request->plan_id) ? 'on' : 'off';
            $member->parent_id = 2; // Assigned to owner with ID 2

            // Handle image upload
            if ($request->hasFile('image')) {
                $extension = $request->file('image')->getClientOriginalExtension();
                $name = \Str::uuid() . '.' . $extension;
                $request->file('image')->storeAs('upload/member/', $name);
                $member->image = $name;
            }

            $member->save();

            // Create membership if plan_id is provided
            if (!empty($request->plan_id)) {
                $plan = MembershipPlan::find($request->plan_id);
                
                if ($plan) {
                    // Calculate expiry date based on plan duration
                    $expiryDate = $this->calculateExpiryDate($plan->duration);
                    
                    $membership = new Membership();
                    $membership->member_id = $member->id;
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

            return redirect()->route('public.register.success')
                ->with('success', __('Registration successful! You can now log in with your credentials.'));

        } catch (\Exception $e) {
            \Log::error('Public member registration error: ' . $e->getMessage());
            
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
}
