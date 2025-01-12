<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FlexibleInvestment extends Model
{
    protected $fillable = [
        'user_id',
        'total_invested',
        'total_profit',
        'current_balance',
        'last_interest_calculation'
    ];

    protected $casts = [
        'total_invested' => 'decimal:2',
        'total_profit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'last_interest_calculation' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(FlexibleInvestmentTransaction::class);
    }

    public function calculatePendingInterest()
    {
        if (!$this->last_interest_calculation) {
            return 0;
        }

        $settings = FlexibleInvestmentSettings::getSettings();
        $lastCalculation = $this->last_interest_calculation;
        $now = Carbon::now();
        
        // Calculate days since last interest calculation
        $days = $lastCalculation->diffInDays($now);
        if ($days === 0) return 0;

        $balance = $this->current_balance;
        $dailyRate = $settings->daily_profit_percentage / 100;

        // Compound interest calculation
        $totalAmount = $balance * pow(1 + $dailyRate, $days);
        return $totalAmount - $balance;
    }
}