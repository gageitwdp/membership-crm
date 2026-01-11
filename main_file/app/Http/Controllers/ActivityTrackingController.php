<?php

namespace App\Http\Controllers;

use App\Models\ActivityTracking;
use App\Models\Event;
use App\Models\Member;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityTrackingController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage activity tracking')) {
            if (\Auth::user()->type == 'member') {
                $user = Auth::user();
                $member = Member::where('user_id', $user->id)->first();
                $activityTrackings = ActivityTracking::where('parent_id', parentId())->where('member_id', $member->id)->orderBy('id', 'desc')->get();
            } else {
                $activityTrackings = ActivityTracking::where('parent_id', parentId())->get();
            }

            return view('activity_tracking.index', compact('activityTrackings'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create activity tracking')) {
            $members = Member::where('parent_id', parentId())->pluck('first_name', 'id');
            $members->prepend('Select Member', '');
            $events = Event::where('parent_id', parentId())->pluck('event_name', 'id');
            $events->prepend('Select Event', '');
            return view('activity_tracking.create', compact('members', 'events'));
        } else {
            return redirect()->back();
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create activity tracking')) {
            $validetor = \Validator::make($request->all(), [
                'member_id' => 'required',
                'event_id' => 'required',
                'check_in' => 'required',
                'check_out' => 'required',
                'duration' => 'required',
            ]);

            if ($validetor->fails()) {
                $messages = $validetor->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $activityTracking = new ActivityTracking();
            $activityTracking->member_id = $request->member_id;
            $activityTracking->event_id = $request->event_id;
            $activityTracking->check_in = $request->check_in;
            $activityTracking->check_out = $request->check_out;
            $activityTracking->duration = $request->duration;
            $activityTracking->parent_id = parentId();
            $activityTracking->save();

            $module = 'activity_tracking';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            $setting = settings();
            $errorMessage = '';

            if (!empty($notification)) {
                $notificationResponse = MessageReplace($notification, $activityTracking->id);

                $data['subject'] = $notificationResponse['subject'];
                $data['message'] = $notificationResponse['message'];
                $data['module'] = $module;
                $data['logo'] = $setting['company_logo'];
                $to = $activityTracking->members->email;

                if ($notification->enabled_email == 1) {
                    $response = commonEmailSend($to, $data);
                    if ($response['status'] == 'error') {
                        $errorMessage = $response['message'];
                    }
                }

                if ($notification->enabled_sms == 1) {
                    $twilio_sid = getSettingsValByName('twilio_sid');
                    if (!empty($twilio_sid)) {
                        send_twilio_msg($activityTracking->members->phone, $response['sms_message']);
                    }
                }
            }

            return redirect()->route('activity-tracking.index')->with('success', __('Activity Tracking Create Successfully.') . '</br>' . $errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show(ActivityTracking $activityTracking)
    {
        if (\Auth::user()->can('show activity tracking')) {
            return view('activity_tracking.show', compact('activityTracking'));
        }
    }


    public function edit(ActivityTracking $activityTracking)
    {
        if (\Auth::user()->can('edit activity tracking')) {
            $members = Member::where('parent_id', parentId())->pluck('first_name', 'id');
            $members->prepend('Select Member', '');
            $events = Event::where('parent_id', parentId())->pluck('event_name', 'id');
            $events->prepend('Select Event', '');
            return view('activity_tracking.edit', compact('members', 'events', 'activityTracking'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function update(Request $request, ActivityTracking $activityTracking)
    {
        if (\Auth::user()->can('create activity tracking')) {
            $validetor = \Validator::make($request->all(), [
                'member_id' => 'required',
                'event_id' => 'required',
                'check_in' => 'required',
                'check_out' => 'required',
                'duration' => 'required',
            ]);

            if ($validetor->fails()) {
                $messages = $validetor->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $activityTracking->member_id = $request->member_id;
            $activityTracking->event_id = $request->event_id;
            $activityTracking->check_in = $request->check_in;
            $activityTracking->check_out = $request->check_out;
            $activityTracking->duration = $request->duration;
            $activityTracking->notes = !empty($request->notes) ? $request->notes : '';
            $activityTracking->save();
            return redirect()->route('activity-tracking.index')->with('success', __('Activity Tracking Update Successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function destroy(ActivityTracking $activityTracking)
    {
        if (\Auth::user()->can('delete activity tracking')) {
            $activityTracking->delete();
            return redirect()->route('activity-tracking.index')->with('success', __('Activity Tracking Delete Successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
