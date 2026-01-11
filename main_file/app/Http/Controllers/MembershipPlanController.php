<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class MembershipPlanController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage membership plan')) {
            $memberShipPlans = MembershipPlan::where('parent_id', parentId())->get();
            $paymentSetting = invoicePaymentSettings(parentId());

            $activeMembership = null;

            if (\Auth::user()->type == 'member') {
                $member = Auth::user();
                $getMember = Member::where('user_id', $member->id)->first();

                // dd($getMember);
                if ($getMember) {
                    $activeMembership = Membership::where('member_id', $getMember->id)
                        ->where('status', 'Active')
                        ->where(function ($q) {
                            $q->whereNull('expiry_date')
                                ->orWhere('expiry_date', '>=', now());
                        })
                        ->latest()
                        ->first();

                    // dd($activeMembership);
                }
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        return view('membership_plan.index', compact('memberShipPlans', 'activeMembership','paymentSetting'));
    }


    public function create()
    {
        if (\Auth::user()->can('create membership plan')) {
            return view('membership_plan.create');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create membership plan')) {

            $validetor = \Validator::make(
                $request->all(),
                [
                    'plan_name' => 'required',
                    'price' => 'required',
                    'duration' => 'required',
                    'billing_frequency' => 'required',

                ]
            );

            if ($validetor->fails()) {
                $messages = $validetor->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $ids = parentId();
            $authUser = \App\Models\User::find($ids);
            $totalMembershiPlan = $authUser->totalMembershiPlan();
            $subscription = Subscription::find($authUser->subscription);
            if ($totalMembershiPlan >= $subscription->membership_plan_limit && $subscription->membership_plan_limit != 0) {
                return redirect()->back()->with('error', __('Your customer limit is over, please upgrade your subscription.'));
            }

            $membershipPlan = new MembershipPlan();
            $membershipPlan->plan_id = $this->planNumbers();
            $membershipPlan->plan_name = $request->plan_name;
            $membershipPlan->price = $request->price;
            $membershipPlan->duration = $request->duration;
            $membershipPlan->plan_description = $request->plan_description;
            $membershipPlan->billing_frequency = $request->billing_frequency;
            // $membershipPlan->benefits = $request->benefits;
            // $membershipPlan->access_level = $request->access_level;
            $membershipPlan->parent_id = parentId();
            $membershipPlan->save();

            return redirect()->route('membership-plan.index')->with('success', __('Membership Plan successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show(MembershipPlan $membershipPlan)
    {
        if (\Auth::user()->can('show membership plan')) {
            return view('membership_plan.show', compact('membershipPlan'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function edit(MembershipPlan $membershipPlan)
    {
        if (\Auth::user()->can('edit membership plan')) {
            return view('membership_plan.edit', compact('membershipPlan'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function update(Request $request, MembershipPlan $membershipPlan)
    {
        if (\Auth::user()->can('edit membership plan')) {

            $validetor = \Validator::make(
                $request->all(),
                [
                    'plan_name' => 'required',
                    'price' => 'required',
                    'duration' => 'required',
                    'billing_frequency' => 'required',

                ]
            );

            if ($validetor->fails()) {
                $messages = $validetor->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $membershipPlan->plan_name = $request->plan_name;
            $membershipPlan->price = $request->price;
            $membershipPlan->duration = $request->duration;
            $membershipPlan->plan_description = $request->plan_description;
            $membershipPlan->billing_frequency = $request->billing_frequency;
            // $membershipPlan->benefits = $request->benefits;
            // $membershipPlan->access_level = $request->access_level;
            $membershipPlan->save();

            return redirect()->route('membership-plan.index')->with('success', __('Membership Plan successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function destroy(MembershipPlan $membershipPlan)
    {
        if (\Auth::user()->can('delete membership plan')) {
            $membershipPlan->delete();
            return redirect()->route('membership-plan.index')->with('success', __('Membership Plan successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function planNumbers()
    {
        $latest = MembershipPlan::where('parent_id', parentId())->latest()->first();
        if (!empty($latest)) {
            return $latest->plan_id + 1;
        } else {
            return 1;
        }
    }

    public function paymentSettings()
    {
        $paymentSetting = invoicePaymentSettings(parentId());
        return $paymentSetting;
    }

    public function payment($encryptedId)
    {
        $id = Crypt::decrypt($encryptedId);

        $membershipPlan = MembershipPlan::findOrFail($id);

        $settings = $this->paymentSettings();
        return view('membership_plan.payment', compact('membershipPlan', 'settings'));
    }
}
