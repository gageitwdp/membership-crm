<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'user_id',
        'first_name',
        'last_name',
        'password',
        'membership_part',
        'email',
        'phone',
        'gender',
        'dob',
        'image',
        'emergency_contact_information',
        'address',
        'note',
        'parent_id',
        'parent_member_id',
        'is_parent',
        'relationship'
    ];

      public function membershipLates()
    {
        return $this->hasOne(Membership::class, 'member_id', 'id')->latestOfMany();
    }

    /**
     * Get the children for a parent member
     */
    public function children()
    {
        return $this->hasMany(Member::class, 'parent_member_id', 'id');
    }

    /**
     * Get the parent member if this is a child
     */
    public function parentMember()
    {
        return $this->belongsTo(Member::class, 'parent_member_id', 'id');
    }


}
