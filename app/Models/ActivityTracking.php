<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'event_id',
        'check_in',
        'check_out',
        'duration',
        'notes',
        'parent_id',
    ];

    public function members(){
        return $this->hasOne(Member::class,'id','member_id');
    }

    public function events(){
        return $this->hasOne(Event::class,'id','event_id');
    }
}
