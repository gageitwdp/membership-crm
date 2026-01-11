<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipPayment;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function income(Request $request)
    {

        $plans = MembershipPlan::where('parent_id', parentId())->pluck('plan_name', 'id');
        $plans->prepend(__('Select plan'), 0);

        $members = Member::where('parent_id', parentId())->pluck('first_name', 'id');
        $members->prepend('Select Member', '');

        $query = MembershipPayment::query()->where('status', 'Paid');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        if (!empty($request->plan_id)) {
            $query->where('plan_id', $request->plan_id);
        }

        $payments = $query->with('member', 'plan')->get();

        $chartData = $payments->groupBy(function ($payment) {
            return $payment->created_at->format('Y-m');
        })->map(function ($group) {
            return $group->sum('amount');
        });

        return view('report.income', [
            'payments'   => $payments,
            'chartData'  => $chartData,
            'plans'  => $plans,
            'members'  => $members,
        ]);
    }

    public function membership(Request $request)
    {

        $plans = MembershipPlan::where('parent_id', parentId())->pluck('plan_name', 'id');
        $plans->prepend(__('Select plan'), 0);

        $members = Member::where('parent_id', parentId())->pluck('first_name', 'id');
        $members->prepend('Select Member', '');

        $query = Membership::query();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        if (!empty($request->plan_id)) {
            $query->where('plan_id', $request->plan_id);
        }

        $memberships = $query->with('members', 'plans')->get();

        $chartData = $memberships->groupBy(function ($payment) {
            return $payment->created_at->format('Y-m');
        })->map(function ($group) {
            return $group->sum('amount');
        });

        return view('report.membership', [
            'memberships'   => $memberships,
            'chartData'  => $chartData,
            'plans'  => $plans,
            'members'  => $members,
        ]);
    }

    public function expense(Request $request)
    {


        $types = ExpenseType::where('parent_id', parentId())->pluck('type', 'id');
        $types->prepend('Select Expense Type', '');

        $query = Expense::query();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }


        $expenses = $query->with('expenseType')->get();

        $chartData = $expenses->groupBy(function ($payment) {
            return $payment->created_at->format('Y-m');
        })->map(function ($group) {
            return $group->sum('amount');
        });

        return view('report.expense', [
            'expenses'   => $expenses,
            'chartData'  => $chartData,
            'types'  => $types,
        ]);
    }
}
