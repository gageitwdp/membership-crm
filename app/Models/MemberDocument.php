<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberDocument extends Model
{
    use HasFactory;
    protected $fillable = [
        'parent_id',
        'member_id',
        'document_name',
        'document_type',
        'document',
        'upload_date',
        'status',
    ];
    public function types()
    {
        return $this->hasOne(DocumentType::class,'id','document_type');
    }
}
