<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralReward extends Model
{
    protected $fillable = [
        'referrer_id',
        'referred_user_id',
        'investment_type',
        'investment_id',
        'reward_amount'
    ];

    protected $casts = [
        'reward_amount' => 'decimal:2'
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}