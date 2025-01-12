<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositRequest extends Model
{
    protected $fillable = [
        'user_id',
        'requested_amount',
        'approved_amount',
        'transaction_id',
        'status',
        'admin_note'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}