<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CryptoInvestment;
use App\Models\FlexibleInvestment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get Crypto Investments
        $cryptoInvestments = CryptoInvestment::with('cryptocurrency')
            ->where('user_id', $user->id)
            ->where('is_withdrawn', false)
            ->get();

        // Calculate crypto investments summary
        $cryptoSummary = [
            'total_invested' => $cryptoInvestments->sum('amount'),
            'total_current_value' => 0,
            'total_pending_profit' => 0,
            'daily_profit' => 0,
            'investments' => []
        ];

        foreach ($cryptoInvestments as $investment) {
            $daysHeld = Carbon::now()->diffInDays($investment->investment_date);
            $dailyProfit = ($investment->amount * $investment->cryptocurrency->daily_profit_percentage) / 100;
            $accruedProfit = $daysHeld * $dailyProfit;
            $currentValue = $investment->amount + $accruedProfit;

            $cryptoSummary['total_current_value'] += $currentValue;
            $cryptoSummary['total_pending_profit'] += $accruedProfit;
            $cryptoSummary['daily_profit'] += $dailyProfit;

            $cryptoSummary['investments'][] = [
                'id' => $investment->id,
                'cryptocurrency' => [
                    'name' => $investment->cryptocurrency->name,
                    'symbol' => $investment->cryptocurrency->symbol,
                    'icon_url' => $investment->cryptocurrency->icon_url,
                ],
                'amount' => $investment->amount,
                'current_value' => $currentValue,
                'profit_earned' => $accruedProfit,
                'daily_profit' => $dailyProfit,
                'daily_profit_percentage' => $investment->cryptocurrency->daily_profit_percentage,
                'investment_date' => $investment->investment_date,
                'maturity_date' => $investment->maturity_date,
                'days_remaining' => max(0, Carbon::now()->diffInDays($investment->maturity_date, false)),
                'is_matured' => $investment->isMatured()
            ];
        }

        // Get Flexible Investment
        $flexibleInvestment = FlexibleInvestment::where('user_id', $user->id)->first();
        $flexibleSummary = [
            'total_invested' => 0,
            'current_balance' => 0,
            'total_profit' => 0,
            'pending_interest' => 0,
            'daily_profit' => 0
        ];

        if ($flexibleInvestment) {
            $pendingInterest = $flexibleInvestment->calculatePendingInterest();
            $settings = \App\Models\FlexibleInvestmentSettings::getSettings();
            $dailyProfit = ($flexibleInvestment->current_balance * $settings->daily_profit_percentage) / 100;

            $flexibleSummary = [
                'total_invested' => $flexibleInvestment->total_invested,
                'current_balance' => $flexibleInvestment->current_balance + $pendingInterest,
                'total_profit' => $flexibleInvestment->total_profit + $pendingInterest,
                'pending_interest' => $pendingInterest,
                'daily_profit' => $dailyProfit,
                'daily_profit_percentage' => $settings->daily_profit_percentage
            ];
        }

        // Calculate overall portfolio summary
        $portfolioSummary = [
            'total_invested' => $cryptoSummary['total_invested'] + $flexibleSummary['total_invested'],
            'total_current_value' => $cryptoSummary['total_current_value'] + $flexibleSummary['current_balance'],
            'total_profit' => $cryptoSummary['total_pending_profit'] + $flexibleSummary['total_profit'],
            'daily_profit' => $cryptoSummary['daily_profit'] + $flexibleSummary['daily_profit'],
            'wallet_balance' => $user->balance
        ];

        return response()->json([
            'summary' => $portfolioSummary,
            'crypto_investments' => $cryptoSummary,
            'flexible_investment' => $flexibleSummary
        ]);
    }
}