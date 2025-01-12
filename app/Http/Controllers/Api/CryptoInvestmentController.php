<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CryptoInvestment;
use App\Models\Cryptocurrency;
use Illuminate\Http\Request;
use App\Services\ReferralService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CryptoInvestmentController extends Controller
{
    
    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }
    
    public function invest(Request $request)
    {
        $request->validate([
            'cryptocurrency_id' => 'required|exists:cryptocurrencies,id',
            'amount' => 'required|numeric|min:0'
        ]);

        $crypto = Cryptocurrency::findOrFail($request->cryptocurrency_id);
        
        // Validate amount limits
        if ($request->amount < $crypto->minimum_deposit || $request->amount > $crypto->maximum_deposit) {
            return response()->json([
                'message' => "Amount must be between {$crypto->minimum_deposit} and {$crypto->maximum_deposit}"
            ], 400);
        }

        // Check user balance
        if ($request->user()->balance < $request->amount) {
            return response()->json([
                'message' => 'Insufficient balance'
            ], 400);
        }

        try {
            DB::transaction(function () use ($request, $crypto) {
                // ... existing investment creation code ...

                $user = $request->user();
                $user->balance -= $request->amount;
                $user->save();
                
                $investment = CryptoInvestment::create([
                    'user_id' => $user->id,
                    'cryptocurrency_id' => $crypto->id,
                    'amount' => $request->amount,
                    'investment_date' => now(),
                    'maturity_date' => now()->addDays($crypto->investment_duration_days)
                ]);

                // Process referral reward
                $this->referralService->processReferralReward(
                    $request->user(),
                    'crypto',
                    $investment->id
                );
            });

            return response()->json([
                'message' => 'Investment created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating investment'
            ], 500);
        }
    }

    public function withdraw(Request $request, CryptoInvestment $investment)
    {
        // Verify ownership
        if ($investment->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if already withdrawn
        if ($investment->is_withdrawn) {
            return response()->json([
                'message' => 'Investment already withdrawn'
            ], 400);
        }

        // Check maturity
        if (!$investment->isMatured()) {
            return response()->json([
                'message' => 'Investment has not matured yet'
            ], 400);
        }

        try {
            DB::transaction(function () use ($investment) {
                // Calculate profit
                $profit = $investment->calculateProfit();
                $totalAmount = $investment->amount + $profit;

                // Update user balance
                $user = $investment->user;
                $user->balance += $totalAmount;
                $user->save();

                // Mark investment as withdrawn
                $investment->update([
                    'is_withdrawn' => true,
                    'profit_earned' => $profit
                ]);
            });

            return response()->json([
                'message' => 'Investment withdrawn successfully',
                'investment' => $investment->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error withdrawing investment'
            ], 500);
        }
    }

    public function userInvestments(Request $request)
    {
        $investments = CryptoInvestment::with('cryptocurrency')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($investments);
    }
}