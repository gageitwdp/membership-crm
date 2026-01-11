<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'expense_id',
        'type',
        'date',
        'amount',
        'receipt',
        'notes',
        'parent_id',
    ];


    public function expenseType(){
        return $this->hasOne(ExpenseType::class, 'id', 'type');
    }
}
