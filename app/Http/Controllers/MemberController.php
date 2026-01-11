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

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Stripe\Plan;

class MemberController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('manage member')) {
            $members = Member::where('parent_id', '=', parentId())->orderBy('id', 'desc')->get();
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        return view('member.index', compact('members'));
    }


    public function create()
    {
        if (\Auth::user()->can('create member')) {
            $membership = MembershipPlan::where('parent_id', parentId())->pluck('plan_name', 'id');
            $membership->prepend('Select Plan', '');
            return view('member.create', compact('membership'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function store(Request $request)
    {

        if (\Auth::user()->can('create member')) {
            $validator = \Validator::make(
                $request->all(),
                [

                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required|email|unique:users',
                    'phone' => 'required',
                    'dob' => 'required',
                    'address' => 'required',

                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $ids = parentId();
            $authUser = \App\Models\User::find($ids);
            $totalMember = $authUser->totalMembers();
            $subscription = Subscription::find($authUser->subscription);
            if ($totalMember >= $subscription->member_limit && $subscription->member_limit != 0) {
                return redirect()->back()->with('error', __('Your member limit is over, please upgrade your subscription.'));
            }

            $userRole = Role::where('name', 'member')->where('parent_id', parentId())->first();

            $user = new User();
            $user->name = $request->first_name;
            $user->email = $request->email;
            $user->phone_number = $request->phone;
            $user->password = Hash::make($request->password);
            $user->type = $userRole->name;
            $user->profile = 'avatar.png';
            $user->lang = 'english';
            $user->parent_id = parentId();
            $user->email_verified_at = now();
            $user->save();
            $user->assignRole($userRole);

            if (!empty($user)) {
                $Member = new Member();
                $Member->member_id = $this->memberNumber();
                $Member->user_id = $user->id;
                $Member->first_name = $request->first_name;
                $Member->last_name = $request->last_name;
                $Member->password = Hash::make($request->password);
                $Member->email = $request->email;
                $Member->phone = $request->phone;
                $Member->dob = $request->dob;
                $Member->address = $request->address;
                $Member->gender = $request->gender;
                $Member->emergency_contact_information = $request->emergency_contact_information;
                $Member->notes = $request->notes;
                $Member->membership_part = $request->membership_part;
                $Member->parent_id = parentId();
                if ($request->hasFile('image')) {
                    $extension = $request->file('image')->getClientOriginalExtension();
                    $name = \Str::uuid() . '.' . $extension;
                    $image = $request->file('image')->storeAs('upload/member/', $name);
                    $Member->image = $name;
                }
                $Member->save();
            }



            if (!empty($request->plan_id)) {
                $membership = new Membership();
                $membership->member_id = $Member->id;
                $membership->plan_id = $request->plan_id;
                $membership->start_date = $request->start_date;
                $membership->expiry_date = $request->expiry_date;
                $membership->status = $request->status;
                $membership->parent_id = parentId();
                $membership->save();

                if (!empty($membership) && $membership->status == 'Active') {
                    return app(MembershipController::class)->renew($request, $membership->id);
                    // return redirect()->route('membership.renew', $membership->id);
                }
                // if (!empty($membership)) {

                //     $payment = new MembershipPayment();
                //     $payment->member_id = $membership->member_id;
                //     $payment->plan_id = $membership->plan_id;
                //     $payment->payment_id = $this->paymentNumber();
                //     $payment->status = 'Unpaid';
                //     $payment->amount = $membership->plans->price;
                //     $payment->parent_id = parentId();
                //     $payment->save();
                // }
            }


            if (!empty($Member->email)) {
                $module = 'member_create';
                $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
                $setting = settings();
                $errorMessage = '';

                if (!empty($notification)) {
                    $notificationResponse = MessageReplace($notification, $Member->id);

                    $data['subject'] = $notificationResponse['subject'];
                    $data['message'] = $notificationResponse['message'];
                    $data['module'] = $module;
                    $data['logo'] = $setting['company_logo'];
                    $to = $request->email;


                    if ($notification->enabled_email == 1) {
                        $response = commonEmailSend($to, $data);
                        if ($response['status'] == 'error') {
                            $errorMessage = $response['message'];
                        }
                    }
                    if ($notification->enabled_sms == 1) {
                        $twilio_sid = getSettingsValByName('twilio_sid');
                        if (!empty($twilio_sid)) {
                            send_twilio_msg($request->phone, $response['sms_message']);
                        }
                    }
                }
                return redirect()->route('member.index')->with('success', __('Member successfully created.') . '</br>' . $errorMessage);
            }


            return redirect()->route('member.index')->with('success', __('Member successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }



    public function show($id)
    {
        if (\Auth::user()->can('show member')) {
            $member = Member::where('parent_id', parentId())->where('id', Crypt::decrypt($id))->first();
            $memberships = Membership::where('parent_id', parentId())
                ->where('member_id', $member->id)
                ->orderBy('id', 'DESC')
                ->get();

            $membershipPayments = MembershipPayment::where('parent_id', parentId())
                ->where('member_id', $member->id)
                ->orderBy('id', 'DESC')
                ->get();

            $lastMembership = $memberships->first();

            $status = 'false';
            if (!empty($lastMembership->expiry_date) && $lastMembership->expiry_date < now()) {
                $status = 'true';
            }

            $documents = MemberDocument::where('member_id', $member->id)->get();
            return view('member.show', compact('member', 'documents', 'memberships', 'membershipPayments', 'lastMembership', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function edit($id)
    {
        if (\Auth::user()->can('edit member')) {
            $member = Member::where('parent_id', parentId())->where('id', Crypt::decrypt($id))->first();
            $membership = MembershipPlan::where('parent_id', parentId())->pluck('plan_name', 'id');
            $plan = Membership::where('parent_id', parentId())->where('member_id', $member->id)->orderBy('id', 'DESC')->first();
            $plans = membershipPlan::where('parent_id', parentId())->where('id', !empty($plan) ? $plan->plan_id : '0')->first();
            return view('member.edit', compact('member', 'membership', 'plans', 'plan'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function update(Request $request, Member $Member)
    {

        if (\Auth::user()->can('create member')) {
            $user = User::find($Member->user_id);

            $validator = \Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'phone' => 'required',
                    'email' => 'required|email|unique:users,email,' . $user->id,
                    'address' => 'required',
                    // 'plan_id' => 'required',
                    // 'start_date' => 'required|date',
                    // 'expiry_date' => 'required|date|after_or_equal:start_date',
                    // 'status' => 'required',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }

            $user->name = $request->first_name;
            $user->email = $request->email;
            $user->phone_number = $request->phone;
            $user->save();

            if (!empty($user)) {
                $Member->first_name = $request->first_name;
                $Member->last_name = $request->last_name;
                $Member->email = $request->email;
                $Member->phone = $request->phone;
                $Member->dob = $request->dob;
                $Member->address = $request->address;
                $Member->gender = $request->gender;
                $Member->emergency_contact_information = $request->emergency_contact_information;
                $Member->notes = $request->notes;
                $Member->membership_part = $request->membership_part;

                if ($request->hasFile('image')) {
                    $extension = $request->file('image')->getClientOriginalExtension();
                    $name = \Str::uuid() . '.' . $extension;
                    $request->file('image')->storeAs('upload/member/', $name);
                    $Member->image = $name;
                }

                $Member->save();
            }

            if (!empty($request->plan_id)) {
                $membership = Membership::where('member_id', $Member->id)->first();
                if (!$membership) {
                    $membership = new Membership();
                    $membership->member_id = $Member->id;
                }
                $membership->parent_id = parentId();

                $membership->plan_id = $request->plan_id;
                $membership->start_date = $request->start_date;
                $membership->expiry_date = $request->expiry_date;
                $membership->status = $request->status;
                $membership->save();

                $payment = MembershipPayment::where('member_id', $Member->id)->first();

                if (!$payment) {
                    $payment = new MembershipPayment();
                }
                $payment->payment_id = $this->paymentNumber();
                $payment->member_id = $membership->member_id;
                $payment->plan_id = $membership->plan_id;
                $payment->status = 'Unpaid';
                $payment->amount = $membership->plan->price ?? 0;
                $payment->save();
            }

            return redirect()->route('member.index')->with('success', __('Member successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }



    public function destroy(Member $Member)
    {
        if (\Auth::user()->can('delete member')) {
            User::where('id', $Member->user_id)->delete();
            MembershipPayment::where('member_id', $Member->id)->delete();
            Membership::where('member_id', $Member->id)->delete();
            $Member->delete();
            return redirect()->route('member.index')->with('success', __('Member successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    // public function memberNumber()
    // {
    //     $latestmember = Member::where('parent_id', parentId())->latest()->first();

    //     // DD($latestmember);
    //     if ($latestmember == null) {
    //         return 1;
    //     } else {
    //         return $latestmember->member_id + 1;
    //     }
    // }
    public function memberNumber()
    {
        $latestMember = Member::where('parent_id', parentId())
            ->orderBy('member_id', 'desc') // fix: order by member_id, not created_at
            ->first();

        return $latestMember ? $latestMember->member_id + 1 : 1;
    }

    public function paymentNumber()
    {
        $latest = MembershipPayment::where('parent_id', parentId())->latest()->first();
        if ($latest == null) {
            return 1;
        } else {
            return $latest->payment_id + 1;
        }
    }

    public function documentCreate($id)
    {
        $member = Member::find($id);
        $types = DocumentType::where('parent_id', parentId())->pluck('type', 'id');
        $types->prepend('Select Type');
        return view('member.document_create', compact('member', 'types'));
    }
    public function documentStore(Request $request, $id)
    {
        $member = Member::find($id);
        $validator = \Validator::make(
            $request->all(),
            [
                'document_name' => 'required',
                'document_type' => 'required',
                'document' => 'required',
                'upload_date' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $document = new MemberDocument();
        $document->member_id = $member->id;
        $document->document_name = $request->document_name;
        $document->document_type = $request->document_type;
        $document->upload_date = $request->upload_date;
        $document->status = 'Pending';
        if ($request->hasFile('document')) {
            $extension = $request->file('document')->getClientOriginalExtension();
            $name = \Str::uuid() . '.' . $extension;
            $documents = $request->file('document')->storeAs('upload/member/document/', $name);
            $document->document = $name;
        }
        $document->parent_id = parentId();
        $document->save();
        return redirect()->back()->with('success', __('Document successfully created.'));
    }
    public function documentEdit($id)
    {
        $document = MemberDocument::find($id);
        $types = DocumentType::where('parent_id', parentId())->pluck('type', 'id');
        $types->prepend('Select Type');
        return view('member.document_edit', compact('document', 'types'));
    }
    public function documentUpdate(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'document_name' => 'required',
                'document_type' => 'required',
                'upload_date' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $document = MemberDocument::find($id);
        $document->document_name = $request->document_name;
        $document->document_type = $request->document_type;
        $document->upload_date = $request->upload_date;
        if ($request->hasFile('document')) {
            $extension = $request->file('document')->getClientOriginalExtension();
            $name = \Str::uuid() . '.' . $extension;
            $documents = $request->file('document')->storeAs('upload/member/document/', $name);
            $document->document = $name;
        }
        $document->save();
        return redirect()->back()->with('success', __('Document successfully updated.'));
    }

    public function documentDestroy($id)
    {
        $document = MemberDocument::find($id);
        $document->delete();
        return redirect()->back()->with('success', __('Document successfully deleted.'));
    }
}
