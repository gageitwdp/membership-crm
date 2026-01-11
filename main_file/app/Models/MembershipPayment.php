<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'member_id',
        'plan_id',
        'payment_method',
        'amount',
        'payment_date',
        'transaction_id',
        'status',
        'invoice_id',
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

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }

    // ✅ Payment belongs to a single Plan
    public function plan()
    {
        return $this->belongsTo(MembershipPlan::class, 'plan_id', 'id');
    }


    public static $payment_method = [
        'cash' => 'Cash',
        'bank' => 'Bank',
        'cheque' => 'Cheque',
        'upi' => 'UPI',
    ];
}
