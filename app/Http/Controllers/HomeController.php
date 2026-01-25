<?php

namespace App\Http\Controllers;

use App\Models\ActivityTracking;
use App\Models\Contact;
use App\Models\Custom;
use App\Models\Event;
use App\Models\FAQ;
use App\Models\HomePage;
use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipPayment;
use App\Models\MembershipPlan;
use App\Models\NoticeBoard;
use App\Models\PackageTransaction;
use App\Models\Page;
use App\Models\Subscription;
use App\Models\Support;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {

        if (\Auth::check()) {
            if (\Auth::user()->type == 'super admin') {
                $result['totalOrganization'] = User::where('type', 'owner')->count();
                $result['totalSubscription'] = Subscription::count();
                $result['totalTransaction'] = PackageTransaction::count();
                $result['totalIncome'] = PackageTransaction::sum('amount');
                $result['totalNote'] = NoticeBoard::where('parent_id', parentId())->count();
                $result['totalContact'] = Contact::where('parent_id', parentId())->count();

                $result['organizationByMonth'] = $this->organizationByMonth();
                $result['paymentByMonth'] = $this->paymentByMonth();

                return view('dashboard.super_admin', compact('result'));
            } else {


                if (\Auth::user()->type == 'member') {
                    $user = Auth::user();

                    $member = Member::where('user_id', $user->id)->first();
                    $result['Membership'] = Membership::where('parent_id', parentId())->where('member_id', $member->id)->first();
                    
                    // Get member's own payments
                    $membershipPayments = MembershipPayment::where('parent_id', parentId())
                        ->where('member_id', $member->id)
                        ->orderBy('id', 'desc')
                        ->get();
                    
                    // If parent, also get all children's payments
                    if ($member && $member->is_parent == 1) {
                        $childrenIds = Member::where('parent_member_id', $member->id)->pluck('id')->toArray();
                        $childPayments = MembershipPayment::where('parent_id', parentId())
                            ->whereIn('member_id', $childrenIds)
                            ->orderBy('id', 'desc')
                            ->get();
                        $membershipPayments = $membershipPayments->merge($childPayments);
                    }
                    
                    $plan = !empty($result['Membership']) ? $result['Membership']->plan_id : '';
                    $result['MembershipPlan'] = MembershipPlan::where('parent_id', parentId())->where('plan_id', $plan)->first();

                    $result['totalActivityTrack'] = ActivityTracking::where('parent_id', parentId())->where('member_id', $user->id)->count();
                    $result['totalMemberbershipPlan'] = MembershipPlan::where('parent_id', parentId())->count();

                    // Get children if this is a parent member
                    $children = [];
                    if ($member && $member->is_parent == 1) {
                        $children = Member::where('parent_member_id', $member->id)
                            ->with(['membershipLates.plans'])
                            ->get();
                    }

                    $memberShipPlans = MembershipPlan::where('parent_id', parentId())->get();
                    $invoicePaymentSettings = invoicePaymentSettings(parentId());

                    $activeMembership = null;

                    if (\Auth::user()->type == 'member') {
                        $getMember = Member::where('user_id', $user->id)->first();

                        if ($getMember) {
                            $activeMembership = Membership::where('member_id', $getMember->id)
                                ->where('status', 'Active')
                                ->where(function ($q) {
                                    $q->whereNull('expiry_date')
                                        ->orWhere('expiry_date', '>=', now());
                                })
                                ->latest()
                                ->first();
                        }
                    }

                    return view('dashboard.member', compact('result', 'user', 'membershipPayments', 'memberShipPlans', 'activeMembership', 'invoicePaymentSettings', 'children', 'member'));
                }

                $result['totalMember'] = Member::where('parent_id', parentId())->count();
                $result['totalMembershipPlan'] = MembershipPlan::where('parent_id', parentId())->count();
                $result['totalExpiredMembership'] = Membership::where('parent_id', parentId())->where('status', 'Expired')->count();
                $result['totalincome'] = MembershipPayment::where('parent_id', parentId())->where('status', 'Paid')->sum('amount');
                $result['totalNote'] = NoticeBoard::where('parent_id', parentId())->count();
                $result['totalContact'] = Contact::where('parent_id', parentId())->count();
                $result['incomeByDay'] = $this->incomeByDay();
                $result['settings'] = settings();


                return view('dashboard.index', compact('result'));
            }
        } else {
            if (!file_exists(setup())) {
                header('location:install');
                die;
            } else {

                $landingPage = getSettingsValByName('landing_page');
                if ($landingPage == 'on') {
                    $subscriptions = Subscription::get();
                    $menus = Page::where('enabled', 1)->get();
                    $FAQs = FAQ::where('enabled', 1)->get();
                    return view('layouts.landing', compact('subscriptions', 'menus', 'FAQs'));
                } else {
                    return redirect()->route('login');
                }
            }
        }
    }

    public function organizationByMonth()
    {
        $start = strtotime(date('Y-01'));
        $end = strtotime(date('Y-12'));

        $currentdate = $start;

        $organization = [];
        while ($currentdate <= $end) {
            $organization['label'][] = date('M-Y', $currentdate);

            $month = date('m', $currentdate);
            $year = date('Y', $currentdate);
            $organization['data'][] = User::where('type', 'owner')->whereMonth('created_at', $month)->whereYear('created_at', $year)->count();
            $currentdate = strtotime('+1 month', $currentdate);
        }


        return $organization;
    }

    public function paymentByMonth()
    {
        $start = strtotime(date('Y-01'));
        $end = strtotime(date('Y-12'));

        $currentdate = $start;

        $payment = [];
        while ($currentdate <= $end) {
            $payment['label'][] = date('M-Y', $currentdate);

            $month = date('m', $currentdate);
            $year = date('Y', $currentdate);
            $payment['data'][] = PackageTransaction::whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('amount');
            $currentdate = strtotime('+1 month', $currentdate);
        }

        return $payment;
    }

    public function incomeByMonth()
    {
        $start = strtotime(date('Y-01'));
        $end = strtotime(date('Y-12'));

        $currentdate = $start;

        $payment = [];
        while ($currentdate <= $end) {
            $payment['label'][] = date('M-Y', $currentdate);

            $month = date('m', $currentdate);
            $year = date('Y', $currentdate);
            $payment['income'][] = InvoicePayment::where('parent_id', parentId())->whereMonth('payment_date', $month)->whereYear('payment_date', $year)->sum('amount');
            $payment['expense'][] = Expense::where('parent_id', parentId())->whereMonth('date', $month)->whereYear('date', $year)->sum('amount');
            $currentdate = strtotime('+1 month', $currentdate);
        }

        return $payment;
    }
    public function incomeByDay()
    {

        $start = strtotime('-15 days');
        $end = strtotime(today()->format('Y-m-d'));
        $currentdate = $start;

        $payment = [];
        while ($currentdate <= $end) {
            $payment['label'][] = date('d-M-Y', $currentdate);

            $date = date('y-m-d', $currentdate);
            // $year = date('Y', $currentdate);
            $payment['data'][] = MembershipPayment::where('parent_id', parentId())->whereDate('payment_date', $date)->sum('amount');
            $currentdate = strtotime('+1 day', $currentdate);
        }

        return $payment;
    }
}
