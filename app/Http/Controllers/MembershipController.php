<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipPayment;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MembershipController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage membership')) {

            Membership::where('parent_id', parentId())
                ->whereDate('expiry_date', '<', \Carbon\Carbon::today())
                ->where('status', '!=', 'Expired')
                ->update(['status' => 'Expired']);

            if (\Auth::user()->type == 'member') {
                $user = Auth::user();
                $member = Member::where('user_id', $user->id)->first();
                $memberships = Membership::where('parent_id', parentId())->where('member_id', $member->id)->orderBy('id', 'desc')->get();
            } else {
                $memberships = Membership::where('parent_id', parentId())->orderBy('id', 'desc')->get();
            }

            return view('membership.index', compact('memberships'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create membership')) {
            $membership = Membership::where('parent_id', parentId())->pluck('member_id');
            $members = Member::where('parent_id', parentId())->whereNotIn('id', $membership)->pluck('first_name', 'id');
            $members->prepend('Select Member', '');

            $members->prepend('Select Member', '');


            $plans = MembershipPlan::where('parent_id', parentId())->pluck('plan_name', 'id');
            $plans->prepend('Select Plan', '');
            return view('membership.create', compact('members', 'plans'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create membership')) {
            $validetor = \Validator::make($request->all(), [
                'member_id' => 'required',
                'plan_id' => 'required',
                'start_date' => 'required',
                'expiry_date' => 'required',
                'status' => 'required',
            ]);
            if ($validetor->fails()) {
                $messages = $validetor->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $membership = new Membership();
            $membership->member_id = $request->member_id;
            $membership->plan_id = $request->plan_id;
            $membership->start_date = $request->start_date;
            $membership->expiry_date = $request->expiry_date;
            $membership->status = $request->status;
            $membership->parent_id = parentId();
            $membership->save();
            if ($membership) {
                $payment = new MembershipPayment();
                $payment->member_id = $membership->member_id;
                $payment->plan_id = $membership->plan_id;
                $payment->payment_id = $this->paymentNumber();
                $payment->status = 'Unpaid';
                $payment->amount = $membership->plans->price;
                $payment->parent_id = parentId();
                $payment->save();
            }
            return redirect()->route('membership.index')->with('success', __('Membership successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show(Membership $membership)
    {
        if (\Auth::user()->can('show membership')) {
            return view('membership.show', compact('membership'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function edit(Membership $membership)
    {
        if (\Auth::user()->can('edit membership')) {
            $members = Member::where('parent_id', parentId())->pluck('first_name', 'id');
            $members->prepend('Select Member', '');
            $plans = MembershipPlan::where('parent_id', parentId())->pluck('plan_name', 'id');
            $plans->prepend('Select Plan', '');
            return view('membership.edit', compact('membership', 'members', 'plans'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function update(Request $request, Membership $membership)
    {
        if (\Auth::user()->can('edit membership')) {

            $validator = \Validator::make($request->all(), [
                'member_id' => 'required',
                'plan_id' => 'required',
                'start_date' => 'required',
                'expiry_date' => 'required',
                'status' => 'required',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $membership->member_id = $request->member_id;
            $membership->plan_id = $request->plan_id;
            $membership->start_date = $request->start_date;
            $membership->expiry_date = $request->expiry_date;
            $membership->status = $request->status;
            $membershipUpdated = $membership->save();

            if ($membershipUpdated) {
                $payment = MembershipPayment::where('member_id', $membership->member_id)->first();

                if (!empty($payment)) {
                    $payment->member_id = $membership->member_id;
                    $payment->plan_id = $membership->plan_id;
                    $payment->status = 'Unpaid';

                    if (!empty($membership->plans)) {
                        $payment->amount = $membership->plans->price;
                    } else {
                        return redirect()->back()->with('error', __('Plan details not found.'));
                    }

                    $payment->parent_id = parentId();
                    $payment->save();
                }
            }

            return redirect()->route('membership.index')->with('success', __('Membership successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function destroy(Membership $membership)
    {

        if (\Auth::user()->can('delete membership')) {
            $membership->delete();
            $payment = MembershipPayment::where('member_id', $membership->member_id)->where('plan_id', $membership->plan_id)->where('parent_id', parentId())->first();
            if (!empty($payment)) {
                $payment->delete();
            }


            return redirect()->route('membership.index')->with('success', __('Membership successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
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

    public function getDuration(Request $request)
    {
        $planId = $request->plan_id;

        $plan = MembershipPlan::where('id', $planId)
            ->where('parent_id', parentId())
            ->pluck('duration')
            ->first();


        if ($plan == 'Day Pass') {
            $duration = 0.033; // Approximately 1 day in months
        } elseif ($plan == 'Monthly') {
            $duration = 1;
        } elseif ($plan == 'Yearly') {
            $duration = 12;
        } elseif ($plan == '3-Month') {
            $duration = 3;
        } elseif ($plan == '6-Month') {
            $duration = 6;
        } else {
            $duration = 0;
        }

        return response()->json(['duration' => $duration]);
    }


    public function renew(Request $request, $id)
    {
        $membership = Membership::findOrFail($id);

        $membership->status = 'Active';
        $membership->save();

        $membershipPayment = new MembershipPayment();
        $membershipPayment->member_id   = $membership->member_id;
        $membershipPayment->plan_id     = $membership->plan_id;
        $membershipPayment->payment_id  = $this->paymentNumber();
        $membershipPayment->status      = 'Unpaid';
        $membershipPayment->amount      = $membership->plans->price;
        $membershipPayment->parent_id   = parentId();
        $membershipPayment->save();

        $transactionID = uniqid('', true);

        $paymentData = [
            'payment_id'     => $membershipPayment->payment_id,
            'member_id'      => $membershipPayment->member_id,
            'plan_id'        => $membershipPayment->plan_id,
            'transaction_id' => $transactionID,
            'payment_type'   => $request->payment_method ?? 'manual',
            'amount'         => $request->amount ?? $membership->plans->price,
            'notes'          => $request->notes ?? '',
        ];

        Membership::addPayment($paymentData);

        return redirect()->back()->with('success', 'Membership renewed successfully!');
    }
}
