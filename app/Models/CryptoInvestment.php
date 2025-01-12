<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CryptoInvestment extends Model
{
    protected $fillable = [
        'user_id',
        'cryptocurrency_id',
        'amount',
        'profit_earned',
        'investment_date',
        'maturity_date',
        'is_withdrawn'
    ];

    protected $casts = [
        'investment_date' => 'datetime',
        'maturity_date' => 'datetime',
        'is_withdrawn' => 'boolean',
        'amount' => 'decimal:2',
        'profit_earned' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cryptocurrency()
    {
        return $this->belongsTo(Cryptocurrency::class);
    }

    public function isMatured()
    {
        return Carbon::now()->gte($this->maturity_date);
    }

    public function calculateProfit()
    {
        if (!$this->isMatured()) {
            return 0;
        }

        $Profit = ($this->amount * $this->cryptocurrency->profit_percentage) / 100;
        return $Profit;
    }
}