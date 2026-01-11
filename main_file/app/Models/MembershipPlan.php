<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'name',
        'price',
        'duration',
        'plan_description',
        'billing_frequency',
        // 'benefits',
        // 'access_level',
    ];



}

