<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlexibleInvestmentTransaction extends Model
{
    protected $fillable = [
        'flexible_investment_id',
        'type',
        'amount',
        'balance_after'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2'
    ];

    public function investment()
    {
        return $this->belongsTo(FlexibleInvestment::class, 'flexible_investment_id');
    }
}