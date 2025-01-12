<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cryptocurrency extends Model
{
    protected $fillable = [
        'name',
        'symbol',
        'icon_url',
        'investment_duration_days',
        'minimum_deposit',
        'maximum_deposit',
        'profit_percentage',
        'wallet_address',
        'description',
        'is_active'
    ];

    protected $casts = [
        'investment_duration_days' => 'integer',
        'minimum_deposit' => 'decimal:2',
        'maximum_deposit' => 'decimal:2',
        'profit_percentage' => 'decimal:2',
        'is_active' => 'boolean'
    ];
}