<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Models\Member;
use App\Models\MemberDocument;
use App\Models\Membership;
use App\Models\MembershipPayment;
use App\Models\MembershipPlan;
use App\Models\Notification;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use App\Support\Tenant;

class MemberController extends Controller
{
    /**
     * Allow guests for public actions (create/store). Keep the rest auth-protected.
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['create', 'store']);
    }

    /**
     * List members (protected).
     */
    public function index()
    {
        if (Auth::user()->can('manage member')) {
            $members = Member::where('parent_id', '=', parentId())
                ->orderBy('id', 'desc')
                ->get();

            return view('member.index', compact('members'));
        }

        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    /**
     * Public: show the new member form (no auth).
     */
    public function create(Request $request)
    {
        // Resolve tenant without requiring Auth::user()
        $tenantId   = Tenant::resolveParentId($request);

        // Load plans for the tenant
        $membership = MembershipPlan::where('parent_id', $tenantId)->pluck('plan_name', 'id');
        $membership = $membership->prepend('Select Plan', '');

        return view('member.create', compact('membership'));
    }

    /**
     * Public: handle form submission and create the member + user account.
     */
    public function store(Request $request)
    {
        // Simple honeypot (bots often fill hidden fields)
        if ($request->filled('website')) {
            return back()->withErrors(['form' => 'Spam detected'])->withInput();
        }

        // Validate input (added password, tighter email rules, sensible limits)
        $validator = Validator::make(
            $request->all(),
            [
                'first_name'   => 'required|string|max:100',
                'last_name'    => 'required|string|max:100',
                'email'        => 'required|email:rfc,dns|max:255|unique:users,email',
                'phone'        => 'required|string|max:30',
                'dob'          => 'required|date',
                'address'      => 'required|string|max:255',
                'password'     => 'required|string|min:8',
                'gender'       => 'nullable|string|max:20',
                'emergency_contact_information' => 'nullable|string|max:255',
                'notes'        => 'nullable|string|max:2000',
                'membership_part' => 'nullable|string|max:255',
                'image'        => 'nullable|image|max:5120', // 5MB
                'plan_id'      => 'nullable|exists:membership_plans,id',
                'start_date'   => 'nullable|date',
                'expiry_date'  => 'nullable|date|after_or_equal:start_date',
                'status'       => 'nullable|in:Active,Inactive,Paid,Unpaid',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }

        // Resolve tenant without requiring Auth::user()
        $tenantId  = Tenant::resolveParentId($request);

        // Enforce subscription member limit if applicable
        $authUser  = User::find($tenantId);
        if ($authUser) {
            $totalMember  = $authUser->totalMembers();
            $subscription = Subscription::find($authUser->subscription);
            if ($subscription && $subscription->member_limit != 0 && $totalMember >= $subscription->member_limit) {
                return redirect()->back()->with('error', __('Your member limit is over, please upgrade your subscription.'));
            }
        }

        // Ensure a "member" role exists for the tenant
        $userRole = Role::where('name', 'member')->where('parent_id', $tenantId)->first();

        // Create User
        $user = new User();
        $user->name              = $request->first_name;
        $user->email             = $request->email;
        $user->phone_number      = $request->phone;
        $user->password          = Hash::make($request->password);
        $user->type              = $userRole ? $userRole->name : 'member';
        $user->profile           = 'avatar.png';
        $user->lang              = 'english';
        $user->parent_id         = $tenantId;
        $user->email_verified_at = now();
        $user->save();

        if ($userRole) {
            $user->assignRole($userRole);
        }

        // Create Member
        $Member = new Member();
        $Member->member_id                     = $this->memberNumber();
        $Member->user_id                       = $user->id;
        $Member->first_name                    = $request->first_name;
        $Member->last_name                     = $request->last_name;
        $Member->password                      = Hash::make($request->password);
        $Member->email                         = $request->email;
        $Member->phone                         = $request->phone;
        $Member->dob                           = $request->dob;
        $Member->address                       = $request->address;
        $Member->gender                        = $request->gender;
        $Member->emergency_contact_information = $request->emergency_contact_information;
        $Member->notes                         = $request->notes;
        $Member->membership_part               = $request->membership_part;
        $Member->parent_id                     = $tenantId;

        if ($request->hasFile('image')) {
            $extension = $request->file('image')->getClientOriginalExtension();
            $name      = (string) Str::uuid() . '.' . $extension;
            $request->file('image')->storeAs('upload/member/', $name);
            $Member->image = $name;
        }

        $Member->save();

        // Optional membership attachment
        if (!empty($request->plan_id)) {
            $membership              = new Membership();
            $membership->member_id   = $Member->id;
            $membership->plan_id     = $request->plan_id;
            $membership->start_date  = $request->start_date;
            $membership->expiry_date = $request->expiry_date;
            $membership->status      = $request->status;
            $membership->parent_id   = $tenantId;
            $membership->save();

            // If immediately Active, trigger renew flow
            if (!empty($membership) && $membership->status === 'Active') {
                return app(MembershipController::class)->renew($request, $membership->id);
                // Or: return redirect()->route('membership.renew', $membership->id);
            }

            // If you want an unpaid payment record by default, uncomment below:
            // $payment = new MembershipPayment();
            // $payment->member_id  = $membership->member_id;
            // $payment->plan_id    = $membership->plan_id;
            // $payment->payment_id = $this->paymentNumber();
            // $payment->status     = 'Unpaid';
            // $payment->amount     = optional($membership->plans)->price ?? 0;
            // $payment->parent_id  = $tenantId;
            // $payment->save();
        }

        // Notifications (email/SMS)
        $errorMessage = '';
        if (!empty($Member->email)) {
            $module       = 'member_create';
            $notification = Notification::where('parent_id', $tenantId)->where('module', $module)->first();
            $setting      = settings();

            if (!empty($notification)) {
                $notificationResponse = MessageReplace($notification, $Member->id) ?? [];
                $data['subject']      = $notificationResponse['subject'] ?? '';
                $data['message']      = $notificationResponse['message'] ?? '';
                $data['module']       = $module;
                $data['logo']         = $setting['company_logo'] ?? null;

                $to = $request->email;
                $response = null;

                if ($notification->enabled_email == 1) {
                    $response = commonEmailSend($to, $data);
                    if (is_array($response) && ($response['status'] ?? '') === 'error') {
                        $errorMessage = $response['message'] ?? '';
                    }
                }

                if ($notification->enabled_sms == 1) {
                    $twilio_sid = getSettingsValByName('twilio_sid');
                    if (!empty($twilio_sid)) {
                        // Prefer the SMS message from notificationResponse, fallback to email response if present
                        $smsMessage = $notificationResponse['sms_message'] ?? ($response['sms_message'] ?? null);
                        if (!empty($smsMessage)) {
                            send_twilio_msg($request->phone, $smsMessage);
                        }
                    }
                }
            }
        }

        return redirect()->route('member.index')
            ->with('success', __('Member successfully created.') . (!empty($errorMessage) ? ('</br>' . $errorMessage) : ''));
    }

    /**
     * Show a member (protected).
     */
    public function show($id)
    {
        if (Auth::user()->can('show member')) {
            $member = Member::where('parent_id', parentId())->where('id', Crypt::decrypt($id))->first();

            if (!$member) {
                return redirect()->back()->with('error', __('Member not found.'));
            }

            $memberships = Membership::where('parent_id', parentId())
                ->where('member_id', $member->id)
                ->orderBy('id', 'DESC')
                ->get();

            $membershipPayments = MembershipPayment::where('parent_id', parentId())
                ->where('member_id', $member->id)
                ->orderBy('id', 'DESC')
                ->get();

            $lastMembership = $memberships->first();
            $status         = 'false';
            if (!empty($lastMembership?->expiry_date) && $lastMembership->expiry_date < now()) {
                $status = 'true';
            }

            $documents = MemberDocument::where('member_id', $member->id)->get();

            return view('member.show', compact('member', 'documents', 'memberships', 'membershipPayments', 'lastMembership', 'status'));
        }

        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    /**
     * Edit member (protected).
     */
    public function edit($id)
    {
        if (Auth::user()->can('edit member')) {
            $member     = Member::where('parent_id', parentId())->where('id', Crypt::decrypt($id))->first();
            if (!$member) {
                return redirect()->back()->with('error', __('Member not found.'));
            }

            $membership = MembershipPlan::where('parent_id', parentId())->pluck('plan_name', 'id');
            $plan       = Membership::where('parent_id', parentId())->where('member_id', $member->id)->orderBy('id', 'DESC')->first();
            // FIX: correct model class name
            $plans      = MembershipPlan::where('parent_id', parentId())->where('id', !empty($plan) ? $plan->plan_id : '0')->first();

            return view('member.edit', compact('member', 'membership', 'plans', 'plan'));
        }

        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    /**
     * Update member (protected).
     */
    public function update(Request $request, Member $Member)
    {
        if (Auth::user()->can('create member')) {
            $user = User::find($Member->user_id);

            $validator = Validator::make(
                $request->all(),
                [
                    'first_name' => 'required|string|max:100',
                    'phone'      => 'required|string|max:30',
                    'email'      => 'required|email:rfc,dns|max:255|unique:users,email,' . ($user->id ?? '0'),
                    'address'    => 'required|string|max:255',
                    // 'plan_id'   => 'nullable|exists:membership_plans,id',
                    // 'start_date'=> 'nullable|date',
                    // 'expiry_date'=> 'nullable|date|after_or_equal:start_date',
                    // 'status'    => 'nullable|in:Active,Inactive,Paid,Unpaid',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }

            // Update user
            $user->name         = $request->first_name;
            $user->email        = $request->email;
            $user->phone_number = $request->phone;
            $user->save();

            // Update member
            $Member->first_name                    = $request->first_name;
            $Member->last_name                     = $request->last_name;
            $Member->email                         = $request->email;
            $Member->phone                         = $request->phone;
            $Member->dob                           = $request->dob;
            $Member->address                       = $request->address;
            $Member->gender                        = $request->gender;
            $Member->emergency_contact_information = $request->emergency_contact_information;
            $Member->notes                         = $request->notes;
            $Member->membership_part               = $request->membership_part;

            if ($request->hasFile('image')) {
                $extension = $request->file('image')->getClientOriginalExtension();
                $name      = (string) Str::uuid() . '.' . $extension;
                $request->file('image')->storeAs('upload/member/', $name);
                $Member->image = $name;
            }

            $Member->save();

            // Optional membership update
            if (!empty($request->plan_id)) {
                $membership = Membership::where('member_id', $Member->id)->first();
                if (!$membership) {
                    $membership            = new Membership();
                    $membership->member_id = $Member->id;
                }

                $membership->parent_id   = parentId();
                $membership->plan_id     = $request->plan_id;
                $membership->start_date  = $request->start_date;
                $membership->expiry_date = $request->expiry_date;
                $membership->status      = $request->status;
                $membership->save();

                $payment = MembershipPayment::where('member_id', $Member->id)->first();
                if (!$payment) {
                    $payment = new MembershipPayment();
                }

                $payment->payment_id = $this->paymentNumber();
                $payment->member_id  = $membership->member_id;
                $payment->plan_id    = $membership->plan_id;
                $payment->status     = 'Unpaid';
                $payment->amount     = optional($membership->plan)->price ?? 0;
                $payment->save();
            }

            return redirect()->route('member.index')->with('success', __('Member successfully updated.'));
        }

        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    /**
     * Delete member (protected).
     */
    public function destroy(Member $Member)
    {
        if (Auth::user()->can('delete member')) {
            User::where('id', $Member->user_id)->delete();
            MembershipPayment::where('member_id', $Member->id)->delete();
            Membership::where('member_id', $Member->id)->delete();
            $Member->delete();

            return redirect()->route('member.index')->with('success', __('Member successfully deleted.'));
        }

        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Generate next sequential member number for the tenant.
     */
    public function memberNumber()
    {
        $latestMember = Member::where('parent_id', parentId())
            ->orderBy('member_id', 'desc') // order by member_id to keep sequence
            ->first();

        return $latestMember ? ($latestMember->member_id + 1) : 1;
    }

    /**
     * Generate next payment number.
     */
    public function paymentNumber()
    {
        $latest = MembershipPayment::where('parent_id', parentId())->latest()->first();
        return $latest ? ($latest->payment_id + 1) : 1;
    }

    /**
     * Document create (protected).
     */
    public function documentCreate($id)
    {
        $member = Member::find($id);
        if (!$member) {
            return redirect()->back()->with('error', __('Member not found.'));
        }

        $types  = DocumentType::where('parent_id', parentId())->pluck('type', 'id');
        $types->prepend('Select Type', '');

        return view('member.document_create', compact('member', 'types'));
    }

    /**
     * Document store (protected).
     */
    public function documentStore(Request $request, $id)
    {
        $member = Member::find($id);
        if (!$member) {
            return redirect()->back()->with('error', __('Member not found.'));
        }

        $validator = Validator::make(
            $request->all(),
            [
                'document_name' => 'required|string|max:255',
                'document_type' => 'required|exists:document_types,id',
                'document'      => 'required|file|max:10240', // 10MB
                'upload_date'   => 'required|date',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $document                  = new MemberDocument();
        $document->member_id       = $member->id;
        $document->document_name   = $request->document_name;
        $document->document_type   = $request->document_type;
        $document->upload_date     = $request->upload_date;
        $document->status          = 'Pending';

        if ($request->hasFile('document')) {
            $extension = $request->file('document')->getClientOriginalExtension();
            $name      = (string) Str::uuid() . '.' . $extension;
            $request->file('document')->storeAs('upload/member/document/', $name);
            $document->document = $name;
        }

        $document->parent_id = parentId();
        $document->save();

        return redirect()->back()->with('success', __('Document successfully created.'));
    }

    /**
     * Document edit (protected).
     */
    public function documentEdit($id)
    {
        $document = MemberDocument::find($id);
        if (!$document) {
            return redirect()->back()->with('error', __('Document not found.'));
        }

        $types    = DocumentType::where('parent_id', parentId())->pluck('type', 'id');
        $types->prepend('Select Type', '');

        return view('member.document_edit', compact('document', 'types'));
    }

    /**
     * Document update (protected).
     */
    public function documentUpdate(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'document_name' => 'required|string|max:255',
                'document_type' => 'required|exists:document_types,id',
                'upload_date'   => 'required|date',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $document = MemberDocument::find($id);
        if (!$document) {
            return redirect()->back()->with('error', __('Document not found.'));
        }

        $document->document_name = $request->document_name;
        $document->document_type = $request->document_type;
        $document->upload_date   = $request->upload_date;

        if ($request->hasFile('document')) {
            $extension = $request->file('document')->getClientOriginalExtension();
            $name      = (string) Str::uuid() . '.' . $extension;
            $request->file('document')->storeAs('upload/member/document/', $name);
            $document->document = $name;
        }

        $document->save();

        return redirect()->back()->with('success', __('Document successfully updated.'));
    }

    /**
     * Document delete (protected).
     */
    public function documentDestroy($id)
    {
        $document = MemberDocument::find($id);
        if (!$document) {
            return redirect()->back()->with('error', __('Document not found.'));
        }

        $document->delete();

        return redirect()->back()->with('success', __('Document successfully deleted.'));
    }
}

