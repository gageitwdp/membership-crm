<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipSuspension extends Model
{
    use HasFactory;

    protected $fillable = [
        'suspension_id',
        'member_id',
        'reason',
        'start_date',
        'end_date',
        'status',
        'parent_id',
    ];

    public function members(){
        return $this->hasOne(Member::class, 'id', 'member_id');
    }
}
