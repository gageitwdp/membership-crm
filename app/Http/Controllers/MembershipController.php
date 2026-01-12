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
            // Expire memberships that have passed expiry_date
            Membership::where('parent_id', parentId())
                ->whereDate('expiry_date', '<', Carbon::today())
                ->where('status', '!=', 'Expired')
                ->update(['status' => 'Expired']);

            if (optional(\Auth::user())->type === 'member') {
                $user = Auth::user();
                $member = Member::where('user_id', optional($user)->id)->first();

                // Guard: if member record is missing, return empty list
                $memberships = $member
                    ? Membership::where('parent_id', parentId())
                        ->where('member_id', $member->id)
                        ->orderBy('id', 'desc')
                        ->get()
                    : collect();
            } else {
                $memberships = Membership::where('parent_id', parentId())
                    ->orderBy('id', 'desc')
                    ->get();
            }
            return view('membership.index', compact('memberships'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function create()
    {
        if (\Auth::user()->can('create membership')) {
            $membershipMemberIds = Membership::where('parent_id', parentId())->pluck('member_id');

            // Build members as id => label (defensive)
            $members = Member::where('parent_id', parentId())
                ->whereNotIn('id', $membershipMemberIds)
                ->pluck('first_name', 'id')
                ->filter() // drop null labels
                ->toArray();
            $members = ['' => 'Select Member'] + $members; // prepend placeholder once

            // Build plans as id => label (defensive)
            $plans = MembershipPlan::where('parent_id', parentId())
                ->get(['id', 'plan_name', 'type'])
                ->filter()
                ->mapWithKeys(function ($p) {
                    $name = $p->plan_name ?? 'Unnamed Plan';
                    $type = $p->type ?? null; // optional type in label
                    $label = $type ? "$name ($type)" : $name;
                    return [$p->id => $label];
                })
                ->toArray();
            $plans = ['' => 'Select Plan'] + $plans;

            return view('membership.create', compact('members', 'plans'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create membership')) {
            $validetor = \Validator::make($request->all(), [
                'member_id'   => ['required', 'integer', 'exists:members,id'],
                'plan_id'     => ['required', 'integer', 'exists:membership_plans,id'],
                'start_date'  => ['required', 'date'],
                'expiry_date' => ['required', 'date', 'after_or_equal:start_date'],
                'status'      => ['required', 'string'],
            ]);
            if ($validetor->fails()) {
                $messages = $validetor->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $membership = new Membership();
            $membership->member_id  = $request->member_id;
            $membership->plan_id    = $request->plan_id;
            $membership->start_date = $request->start_date;
            $membership->expiry_date= $request->expiry_date;
            $membership->status     = $request->status;
            $membership->parent_id  = parentId();
            $membership->save();

            if ($membership) {
                $payment = new MembershipPayment();
                $payment->member_id  = $membership->member_id;
                $payment->plan_id    = $membership->plan_id;
                $payment->payment_id = $this->paymentNumber();
                $payment->status     = 'Unpaid';
                // Defensive: derive amount from the plan directly
                $planPrice = MembershipPlan::where('id', $membership->plan_id)->value('price');
                $payment->amount     = $planPrice ?? 0;
                $payment->parent_id  = parentId();
                $payment->save();
            }

            return redirect()->route('membership.index')->with('success', __('Membership successfully created.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function show(Membership $membership)
    {
        if (\Auth::user()->can('show membership')) {
            return view('membership.show', compact('membership'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function edit(Membership $membership)
    {
        if (\Auth::user()->can('edit membership')) {
            $members = Member::where('parent_id', parentId())
                ->pluck('first_name', 'id')
                ->filter()
                ->toArray();
            $members = ['' => 'Select Member'] + $members;

            $plans = MembershipPlan::where('parent_id', parentId())
                ->get(['id','plan_name','type'])
                ->mapWithKeys(function ($p) {
                    $name = $p->plan_name ?? 'Unnamed Plan';
                    $type = $p->type ?? null;
                    $label = $type ? "$name ($type)" : $name;
                    return [$p->id => $label];
                })
                ->toArray();
            $plans = ['' => 'Select Plan'] + $plans;

            return view('membership.edit', compact('membership', 'members', 'plans'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function update(Request $request, Membership $membership)
    {
        if (\Auth::user()->can('edit membership')) {
            $validator = \Validator::make($request->all(), [
                'member_id'   => ['required', 'integer', 'exists:members,id'],
                'plan_id'     => ['required', 'integer', 'exists:membership_plans,id'],
                'start_date'  => ['required', 'date'],
                'expiry_date' => ['required', 'date', 'after_or_equal:start_date'],
                'status'      => ['required', 'string'],
            ]);
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $membership->member_id  = $request->member_id;
            $membership->plan_id    = $request->plan_id;
            $membership->start_date = $request->start_date;
            $membership->expiry_date= $request->expiry_date;
            $membership->status     = $request->status;
            $membershipUpdated = $membership->save();

            if ($membershipUpdated) {
                $payment = MembershipPayment::where('member_id', $membership->member_id)
                    ->where('plan_id', $membership->plan_id)
                    ->where('parent_id', parentId())
                    ->latest()
                    ->first();

                if (!empty($payment)) {
                    $payment->member_id = $membership->member_id;
                    $payment->plan_id   = $membership->plan_id;
                    $payment->status    = 'Unpaid';

                    // Defensive: get price from plan table; abort with message if missing
                    $planPrice = MembershipPlan::where('id', $membership->plan_id)->value('price');
                    if ($planPrice === null) {
                        return redirect()->back()->with('error', __('Plan details not found.'));
                    }
                    $payment->amount    = $planPrice;
                    $payment->parent_id = parentId();
                    $payment->save();
                }
            }

            return redirect()->route('membership.index')->with('success', __('Membership successfully updated.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function destroy(Membership $membership)
    {
        if (\Auth::user()->can('delete membership')) {
            $membership->delete();

            $payment = MembershipPayment::where('member_id', $membership->member_id)
                ->where('plan_id', $membership->plan_id)
                ->where('parent_id', parentId())
                ->latest()
                ->first();

            if (!empty($payment)) {
                $payment->delete();
            }
            return redirect()->route('membership.index')->with('success', __('Membership successfully deleted.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function paymentNumber()
    {
        $latest = MembershipPayment::where('parent_id', parentId())->latest()->first();
        return $latest == null ? 1 : ($latest->payment_id + 1);
    }

    public function getDuration(Request $request)
    {
        $planId = $request->plan_id;
        $plan = MembershipPlan::where('id', $planId)
            ->where('parent_id', parentId())
            ->value('duration');

        switch ($plan) {
            case 'Monthly':
                $duration = 1;
                break;
            case 'Yearly':
                $duration = 12;
                break;
            case '3-Month':
                $duration = 3;
                break;
            case '6-Month':
                $duration = 6;
                break;
            default:
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
        $membershipPayment->member_id  = $membership->member_id;
        $membershipPayment->plan_id    = $membership->plan_id;
        $membershipPayment->payment_id = $this->paymentNumber();
        $membershipPayment->status     = 'Unpaid';
        // Defensive: derive amount from plan
        $planPrice = MembershipPlan::where('id', $membership->plan_id)->value('price');
        $membershipPayment->amount     = $planPrice ?? 0;
        $membershipPayment->parent_id  = parentId();
        $membershipPayment->save();

        $transactionID = uniqid('', true);
        $paymentData = [
            'payment_id'    => $membershipPayment->payment_id,
            'member_id'     => $membershipPayment->member_id,
            'plan_id'       => $membershipPayment->plan_id,
            'transaction_id'=> $transactionID,
            'payment_type'  => $request->payment_method ?? 'manual',
            'amount'        => $request->amount ?? ($planPrice ?? 0),
            'notes'         => $request->notes ?? '',
        ];
        Membership::addPayment($paymentData);

        return redirect()->back()->with('success', 'Membership renewed successfully!');
    }
}

