<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FlexibleInvestment;
use App\Models\FlexibleInvestmentSettings;
use App\Models\FlexibleInvestmentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ReferralService;
use Carbon\Carbon;

class FlexibleInvestmentController extends Controller
{
    
    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }
    
    
    public function deposit(Request $request)
    {
        $settings = FlexibleInvestmentSettings::getSettings();
        if (!$settings || !$settings->is_active) {
            return response()->json(['message' => 'Flexible investment is currently unavailable'], 400);
        }

        $request->validate([
            'amount' => "required|numeric|min:{$settings->minimum_deposit}|max:{$settings->maximum_deposit}"
        ]);

        if ($request->user()->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        try {
            DB::transaction(function () use ($request) {
                // Deduct from user balance
                $user = $request->user();
                $user->balance -= $request->amount;
                $user->save();

                // Get or create flexible investment
                $investment = FlexibleInvestment::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'last_interest_calculation' => now(),
                        'current_balance' => 0
                    ]
                );

                // Calculate and add pending interest before deposit
                $pendingInterest = $investment->calculatePendingInterest();
                if ($pendingInterest > 0) {
                    $investment->total_profit += $pendingInterest;
                    $investment->current_balance += $pendingInterest;
                    
                    // Record interest transaction
                    FlexibleInvestmentTransaction::create([
                        'flexible_investment_id' => $investment->id,
                        'type' => 'interest',
                        'amount' => $pendingInterest,
                        'balance_after' => $investment->current_balance
                    ]);
                }

                // Add deposit
                $investment->total_invested += $request->amount;
                $investment->current_balance += $request->amount;
                $investment->last_interest_calculation = now();
                $investment->save();

                // Record deposit transaction
                FlexibleInvestmentTransaction::create([
                    'flexible_investment_id' => $investment->id,
                    'type' => 'deposit',
                    'amount' => $request->amount,
                    'balance_after' => $investment->current_balance
                ]);
                
                // Process referral reward
                $this->referralService->processReferralReward(
                    $request->user(),
                    'flexible',
                    $transaction->id
                );
            });

            return response()->json(['message' => 'Deposit successful']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error processing deposit'], 500);
        }
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $investment = FlexibleInvestment::where('user_id', $request->user()->id)->first();
        
        if (!$investment) {
            return response()->json(['message' => 'No investment found'], 404);
        }

        // Calculate pending interest
        $pendingInterest = $investment->calculatePendingInterest();
        $totalAvailable = $investment->current_balance + $pendingInterest;

        if ($request->amount > $totalAvailable) {
            return response()->json(['message' => 'Insufficient investment balance'], 400);
        }

        try {
            DB::transaction(function () use ($request, $investment, $pendingInterest) {
                // Add pending interest first
                if ($pendingInterest > 0) {
                    $investment->total_profit += $pendingInterest;
                    $investment->current_balance += $pendingInterest;
                    
                    FlexibleInvestmentTransaction::create([
                        'flexible_investment_id' => $investment->id,
                        'type' => 'interest',
                        'amount' => $pendingInterest,
                        'balance_after' => $investment->current_balance
                    ]);
                }

                // Process withdrawal
                $investment->current_balance -= $request->amount;
                $investment->last_interest_calculation = now();
                $investment->save();

                // Add to user balance
                $user = $request->user();
                $user->balance += $request->amount;
                $user->save();

                // Record withdrawal transaction
                FlexibleInvestmentTransaction::create([
                    'flexible_investment_id' => $investment->id,
                    'type' => 'withdraw',
                    'amount' => -$request->amount,
                    'balance_after' => $investment->current_balance
                ]);
            });

            return response()->json(['message' => 'Withdrawal successful']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error processing withdrawal'], 500);
        }
    }

    public function getBalance(Request $request)
    {
        $investment = FlexibleInvestment::where('user_id', $request->user()->id)->first();
        
        if (!$investment) {
            return response()->json([
                'total_invested' => 0,
                'total_profit' => 0,
                'current_balance' => 0,
                'pending_interest' => 0
            ]);
        }

        $pendingInterest = $investment->calculatePendingInterest();

        return response()->json([
            'total_invested' => $investment->total_invested,
            'total_profit' => $investment->total_profit + $pendingInterest,
            'current_balance' => $investment->current_balance + $pendingInterest,
            'pending_interest' => $pendingInterest
        ]);
    }

    public function getTransactions(Request $request)
    {
        $investment = FlexibleInvestment::where('user_id', $request->user()->id)->first();
        
        if (!$investment) {
            return response()->json(['transactions' => []]);
        }

        $transactions = $investment->transactions()
            ->latest()
            ->paginate(20);

        return response()->json($transactions);
    }
}