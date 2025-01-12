<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'withdrawal_details',
        'status',
        'admin_note',
        'transaction_id'
    ];

    protected $casts = [
        'withdrawal_details' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}