<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipSuspension;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MembershipSuspensionController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage membership suspension')) {


            if (\Auth::user()->type == 'member') {
                $user = Auth::user();
                $member = Member::where('user_id', $user->id)->first();
                $membershipSuspensions = MembershipSuspension::where('parent_id', parentId())->where('member_id', $member->id)->orderBy('id', 'desc')->get();
            } else {
                $membershipSuspensions = MembershipSuspension::where('parent_id', parentId())->get();
            }

            return view('membership_suspension.index', compact('membershipSuspensions'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create membership suspension')) {
            $member = Member::where('parent_id', parentId())->pluck('first_name', 'id');
            $member->prepend('Select Member', '');
            return view('membership_suspension.create', compact('member'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create membership suspension')) {
            $validetor = \Validator::make($request->all(), [
                'member_id' => 'required',
                'reason' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
                'status' => 'required',
            ]);
            if ($validetor->fails()) {
                $messages = $validetor->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $membershipSuspension = new MembershipSuspension();
            $membershipSuspension->suspension_id = $this->suspensionNumber();
            $membershipSuspension->member_id = $request->member_id;
            $membershipSuspension->reason = $request->reason;
            $membershipSuspension->start_date = $request->start_date;
            $membershipSuspension->end_date = $request->end_date;
            $membershipSuspension->status = $request->status;
            $membershipSuspension->parent_id = parentId();
            $membershipSuspension->save();


            $module = 'membership_suspension';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            $setting = settings();
            $errorMessage = '';

            if (!empty($notification)) {
                $notificationResponse = MessageReplace($notification, $membershipSuspension->id);

                $data['subject'] = $notificationResponse['subject'];
                $data['message'] = $notificationResponse['message'];
                $data['module'] = $module;
                $data['logo'] = $setting['company_logo'];
                $to = $membershipSuspension->members->email;

                if ($notification->enabled_email == 1) {
                    $response = commonEmailSend($to, $data);

                    if ($response['status'] == 'error') {
                        $errorMessage = $response['message'];
                    }
                }
                if ($notification->enabled_sms == 1) {
                    $twilio_sid = getSettingsValByName('twilio_sid');
                    if (!empty($twilio_sid)) {
                        send_twilio_msg($membershipSuspension->members->phone, $response['sms_message']);
                    }
                }
            }

            return redirect()->route('membership-suspension.index')->with('success', __('Membership Suspension successfully created.') . '</br>' . $errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show(MembershipSuspension $membershipSuspension)
    {
        if (\Auth::user()->can('show membership suspension')) {
            return view('membership_suspension.show', compact('membershipSuspension'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function edit(MembershipSuspension $membershipSuspension)
    {
        if (\Auth::user()->can('edit membership suspension')) {
            $member = Member::where('parent_id', parentId())->pluck('first_name', 'id');
            return view('membership_suspension.edit', compact('member', 'membershipSuspension'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function update(Request $request, MembershipSuspension $membershipSuspension)
    {
        if (\Auth::user()->can('edit membership suspension')) {
            $validetor = \Validator::make($request->all(), [
                'member_id' => 'required',
                'reason' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
                'status' => 'required',
            ]);
            if ($validetor->fails()) {
                $messages = $validetor->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $membershipSuspension->member_id = $request->member_id;
            $membershipSuspension->reason = $request->reason;
            $membershipSuspension->start_date = $request->start_date;
            $membershipSuspension->end_date = $request->end_date;
            $membershipSuspension->status = $request->status;
            $membershipSuspension->save();
            return redirect()->route('membership-suspension.index')->with('success', __('Membership Suspension successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function destroy(MembershipSuspension $membershipSuspension)
    {
        if (\Auth::user()->can('delete membership suspension')) {
            $membershipSuspension->delete();
            return redirect()->route('membership-suspension.index')->with('success', __('Membership Suspension successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function suspensionNumber()
    {
        $latest = MembershipSuspension::where('parent_id', parentId())->latest()->first();
        if ($latest == null) {
            $number = 1;
        } else {
            $number = $latest->suspension_id + 1;
        }
        return $number;
    }
}
