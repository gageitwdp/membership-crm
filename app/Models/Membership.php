<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [

        'member_id',
        'plan_id',
        'start_date',
        'expiry_date',
        'status',
        'parent_id',

    ];

    public function members()
    {
        return $this->hasOne(Member::class, 'id', 'member_id');
    }

    public function plans()
    {
        return $this->hasOne(MembershipPlan::class, 'id', 'plan_id');
    }

    public function latestPayment()
    {
        return $this->hasOne(MembershipPayment::class, 'member_id', 'member_id')->latestOfMany();
    }


    // public static function addPayment($data, $type)
    // {
    //     if ($data['payment_type'] == 'Bank Transfer') {
    //         $status = 'Pending';
    //     } else {
    //         $status = $data['amount'] > 0 ? 'Paid' : 'Unpaid';
    //     }

    //     $payment = MembershipPayment::where('member_id', $data['member_id'])
    //         ->where('plan_id', $data['plan_id'])
    //         ->where('status', 'Unpaid')
    //         ->first();

    //     if ($payment) {
    //         $payment->update([
    //             'transaction_id' => $data['transaction_id'],
    //             'payment_method' => $data['payment_type'],
    //             'amount'         => $data['amount'],
    //             'payment_date'   => date('Y-m-d'),
    //             'receipt'        => !empty($data['receipt']) ? $data['receipt'] : '',
    //             'notes'          => $data['payment_type'] . ' payment',
    //             'status'         => $status,
    //         ]);
    //     } else {
    //         $payment = MembershipPayment::create([
    //             'payment_id'     => $data['payment_id'],
    //             'member_id'      => $data['member_id'],
    //             'plan_id'        => $data['plan_id'],
    //             'transaction_id' => $data['transaction_id'],
    //             'payment_method' => $data['payment_type'],
    //             'amount'         => $data['amount'],
    //             'payment_date'   => date('Y-m-d'),
    //             'receipt'        => !empty($data['receipt']) ? $data['receipt'] : '',
    //             'notes'          => $data['payment_type'] . ' payment',
    //             'status'         => $status,
    //             'parent_id'      => parentId(),
    //         ]);
    //     }

    //     $plan = MembershipPlan::find($data['plan_id']);


    //     if ($plan) {
    //         if ($plan->duration == 'Monthly') {
    //             $expiry_date = Carbon::now()->addMonths(1)->format('Y-m-d');
    //             // dd($expiry_date);
    //         } elseif ($plan->duration == '3-Month') {
    //             $expiry_date = Carbon::now()->addMonths(3)->format('Y-m-d');
    //         } elseif ($plan->duration == '6-Month') {
    //             $expiry_date = Carbon::now()->addMonths(6)->format('Y-m-d');
    //         } elseif ($plan->duration == 'Yearly') {
    //             $expiry_date = Carbon::now()->addYears(1)->format('Y-m-d');
    //         } else {
    //             $expiry_date = null;
    //         }


    //         Membership::where('member_id', $data['member_id'])
    //             ->where('status', 'Active')
    //             ->where('parent_id', parentId())
    //             ->update([
    //                 'status' => 'Expired',
    //                 'expiry_date' => now(),
    //             ]);


    //         $membership = new Membership();
    //         $membership->member_id   = $data['member_id'];
    //         $membership->plan_id     = $data['plan_id'];
    //         $membership->start_date  = now();
    //         $membership->expiry_date = $expiry_date;
    //         $membership->status      = 'Active';
    //         $membership->parent_id  = parentId();
    //         $membership->save();
    //     }

    //     return $payment;
    // }


    public static function addPayment($data)
    {
        // --- Decide payment status ---
        if ($data['payment_type'] == 'Bank Transfer') {
            $status = 'Pending';
        } else {
            $status = $data['amount'] > 0 ? 'Paid' : 'Unpaid';
        }

        // --- Handle Payment ---
        $payment = MembershipPayment::where('member_id', $data['member_id'])
            ->where('plan_id', $data['plan_id'])
            ->where('status', 'Unpaid')
            ->first();

        if ($payment) {
            // Update existing unpaid payment
            $payment->update([
                'transaction_id' => $data['transaction_id'],
                'payment_method' => $data['payment_type'],
                'amount'         => $data['amount'],
                'payment_date'   => now()->format('Y-m-d'),
                'receipt'        => !empty($data['receipt']) ? $data['receipt'] : '',
                'notes'          => $data['payment_type'] . ' payment',
                'status'         => $status,
            ]);
        } else {
            // Create new payment record
            $payment = MembershipPayment::create([
                'payment_id'     => $data['payment_id'],
                'member_id'      => $data['member_id'],
                'plan_id'        => $data['plan_id'],
                'transaction_id' => $data['transaction_id'],
                'payment_method' => $data['payment_type'],
                'amount'         => $data['amount'],
                'payment_date'   => now()->format('Y-m-d'),
                'receipt'        => !empty($data['receipt']) ? $data['receipt'] : '',
                'notes'          => $data['payment_type'] . ' payment',
                'status'         => $status,
                'parent_id'      => parentId(),
            ]);
        }

        // --- Handle Membership ---
        $plan = MembershipPlan::find($data['plan_id']);
        $expiry_date = null;

        if ($plan) {
            if ($plan->duration == 'Day Pass') {
                $expiry_date = Carbon::now()->addDays(1)->format('Y-m-d');
            } elseif ($plan->duration == 'Monthly') {
                $expiry_date = Carbon::now()->addMonths(1)->format('Y-m-d');
            } elseif ($plan->duration == '3-Month') {
                $expiry_date = Carbon::now()->addMonths(3)->format('Y-m-d');
            } elseif ($plan->duration == '6-Month') {
                $expiry_date = Carbon::now()->addMonths(6)->format('Y-m-d');
            } elseif ($plan->duration == 'Yearly') {
                $expiry_date = Carbon::now()->addYears(1)->format('Y-m-d');
            }
        }

        $activeMembership = Membership::where('member_id', $data['member_id'])
            ->where('status', 'Active')
            ->where('parent_id', parentId())
            ->first();

        if ($activeMembership) {
            if ($activeMembership->plan_id == $data['plan_id']) {
                // $activeMembership->update([
                //     'start_date'  => now(),
                //     'expiry_date' => $expiry_date,
                //     'status'      => 'Active',
                // ]);

                Membership::create([
                    'member_id'   => $data['member_id'],
                    'plan_id'     => $data['plan_id'],
                    'start_date'  => now(),
                    'expiry_date' => $expiry_date,
                    'status'      => 'Active',
                    'parent_id'   => parentId(),
                ]);
            } else {
                $activeMembership->update([
                    'status'      => 'Expired',
                    'expiry_date' => now(),
                ]);

                Membership::create([
                    'member_id'   => $data['member_id'],
                    'plan_id'     => $data['plan_id'],
                    'start_date'  => now(),
                    'expiry_date' => $expiry_date,
                    'status'      => 'Active',
                    'parent_id'   => parentId(),
                ]);
            }
        } else {
            Membership::create([
                'member_id'   => $data['member_id'],
                'plan_id'     => $data['plan_id'],
                'start_date'  => now(),
                'expiry_date' => $expiry_date,
                'status'      => 'Active',
                'parent_id'   => parentId(),
            ]);
        }

        return $payment;
    }
}
