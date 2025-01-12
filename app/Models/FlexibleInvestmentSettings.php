<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlexibleInvestmentSettings extends Model
{
    protected $fillable = [
        'minimum_deposit',
        'maximum_deposit',
        'daily_profit_percentage',
        'is_active'
    ];

    protected $casts = [
        'minimum_deposit' => 'decimal:2',
        'maximum_deposit' => 'decimal:2',
        'daily_profit_percentage' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public static function getSettings()
    {
        return static::where('is_active', true)->latest()->first();
    }
}