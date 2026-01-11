<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Member;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class EventController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage event')) {

            $user = Auth::user();
            $member = Member::where('user_id',$user->id)->first();
            if ($user->type == 'member') {
                $events = Event::select('events.*')
                    ->join('activity_trackings', 'events.id', '=', 'activity_trackings.event_id')
                    ->where('activity_trackings.member_id', $member->id)
                    ->where('events.parent_id', parentId())
                    ->orderBy('events.id', 'desc')
                    ->get();
            } else {
                $events = Event::where('parent_id', parentId())->orderBy('id', 'desc')->get();
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        return view('event.index', compact('events'));
    }

    public function create()
    {
        if (\Auth::user()->can('create event')) {
            return view('event.create');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create event')) {
            $validetor = \Validator::make(
                $request->all(),
                [
                    'event_name' => 'required',
                    'date_time' => 'required',
                    'location' => 'required',
                    'max_participant' => 'required',
                    'date_time' => 'required',
                ]
            );
            if ($validetor->fails()) {
                $messages = $validetor->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $event = new Event();
            $event->event_id = $this->eventNumber();
            $event->event_name = $request->event_name;
            $event->date_time = $request->date_time;
            $event->location = $request->location;
            $event->max_participant = $request->max_participant;
            $event->registration_deadline = $request->registration_deadline;
            $event->availability_status = $request->availability_status;
            $event->description = $request->description;
            $event->parent_id = parentId();
            $event->save();


            $module = 'event_create';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            $setting = settings();
            $errorMessage = '';


            if (!empty($notification)) {
                $notificationResponse = MessageReplace($notification, $event->id);

                $data['subject'] = $notificationResponse['subject'];
                $data['message'] = $notificationResponse['message'];
                $data['module'] = $module;
                $data['logo'] = $setting['company_logo'];

                $to = Member::where('parent_id', parentId())->pluck('email');
                $to_phone = Member::where('parent_id', parentId())->pluck('phone');

                if ($notification->enabled_email == 1) {
                    $response = commonEmailSend($to, $data);

                    if ($response['status'] == 'error') {
                        $errorMessage = $response['message'];
                    }
                }
                if ($notification->enabled_sms == 1) {
                    $twilio_sid = getSettingsValByName('twilio_sid');
                    if (!empty($twilio_sid)) {
                        send_twilio_msg($to_phone, $response['sms_message']);
                    }
                }
            }

            return redirect()->route('event.index')->with('success', __('Event Create Successfully.') . '</br>' . $errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show(Event $event)
    {
        if (\Auth::user()->can('show event')) {
            return view('event.show', compact('event'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function edit(Event $event)
    {
        if (\Auth::user()->can('edit event')) {
            return view('event.edit', compact('event'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function update(Request $request, Event $event)
    {
        if (\Auth::user()->can('edit event')) {
            $validetor = \Validator::make(
                $request->all(),
                [
                    'event_name' => 'required',
                    'date_time' => 'required',
                    'location' => 'required',
                    'max_participant' => 'required',
                    'registration_deadline' => 'required',
                ]
            );
            if ($validetor->fails()) {
                $messages = $validetor->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $event->event_name = $request->event_name;
            $event->date_time = $request->date_time;
            $event->location = $request->location;
            $event->max_participant = $request->max_participant;
            $event->registration_deadline = $request->registration_deadline;
            $event->availability_status = $request->availability_status;
            $event->description = $request->description;
            $event->parent_id = parentId();
            $event->save();
            return redirect()->route('event.index')->with('success', __('Event Update Successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function destroy(Event $event)
    {
        if (\Auth::user()->can('delete event')) {
            $event->delete();
            return redirect()->route('event.index')->with('success', __('Event Delete Successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function eventNumber()
    {
        $latest = Event::where('parent_id', parentId())->latest()->first();
        if ($latest == null) {
            return 1;
        } else {
            return $latest->event_id + 1;
        }
    }

    public function calendar()
    {
        if (\Auth::user()->can('event calendar')) {
            $couriers = Event::where('parent_id', parentId())->get();
            $eventData = $currentMonth = [];
            foreach ($couriers as $courier) {
                $customer = $courier->event_name;
                $courierId = $courier->event_id;
                $event = [
                    'title' => $courierId . ' - ' . $customer,
                    'start' =>  date("Y-m-d", strtotime($courier->date_time)),
                    'end' =>  date("Y-m-d", strtotime($courier->registration_deadline)),
                    // 'url' =>    route('event.show', $courier->id),
                ];
                $eventData[] = $event;

                $currentMonthEvent = [
                    'title' => $courierId . ' - ' . $customer,
                    'date_time' => dateFormat($courier->date_time),
                    'delivery_date'   => dateFormat($courier->registration_deadline),
                    'status'   => $courier->availability_status,
                    'participant'   => $courier->max_participant,
                ];
                $currentMonth[] = $currentMonthEvent;
            }

            return view('event.calendar', compact('eventData', 'couriers', 'currentMonth'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
