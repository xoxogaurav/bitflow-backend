<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CryptoInvestment;
use App\Models\DepositRequest;
use App\Models\WithdrawalRequest;
use App\Models\FlexibleInvestmentTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 15);
        
        // Get deposits
        $deposits = DepositRequest::where('user_id', $user->id)
            ->select([
                'id',
                'requested_amount as amount',
                'approved_amount',
                'status',
                'transaction_id',
                'created_at',
                \DB::raw("'deposit' as type")
            ]);

        // Get withdrawals
        $withdrawals = WithdrawalRequest::where('user_id', $user->id)
            ->select([
                'id',
                'amount',
                \DB::raw('NULL as approved_amount'),
                'status',
                'transaction_id',
                'created_at',
                \DB::raw("'withdrawal' as type")
            ]);

        // Get crypto investments
        $cryptoInvestments = CryptoInvestment::with('cryptocurrency:id,name,symbol')
            ->where('user_id', $user->id)
            ->select([
                'id',
                'amount',
                'cryptocurrency_id',
                'profit_earned',
                'investment_date as created_at',
                'is_withdrawn',
                \DB::raw("'crypto_investment' as type")
            ]);

        // Get flexible investment transactions
        $flexibleTransactions = FlexibleInvestmentTransaction::whereHas('investment', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->select([
            'id',
            'amount',
            \DB::raw('NULL as approved_amount'),
            \DB::raw("'completed' as status"),
            \DB::raw('NULL as transaction_id'),
            'created_at',
            \DB::raw("CONCAT('flexible_', type) as type")
        ]);

        // Combine all transactions and paginate
        $transactions = $deposits->union($withdrawals)
            ->union($cryptoInvestments)
            ->union($flexibleTransactions)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Format the response
        $formattedTransactions = $transactions->map(function ($transaction) {
            $data = [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'status' => $transaction->status ?? 'completed',
                'date' => Carbon::parse($transaction->created_at)->format('Y-m-d H:i:s'),
                'details' => []
            ];

            switch ($transaction->type) {
                case 'deposit':
                    $data['details'] = [
                        'requested_amount' => $transaction->amount,
                        'approved_amount' => $transaction->approved_amount,
                        'transaction_id' => $transaction->transaction_id
                    ];
                    break;

                case 'withdrawal':
                    $data['details'] = [
                        'transaction_id' => $transaction->transaction_id
                    ];
                    break;

                case 'crypto_investment':
                    $data['details'] = [
                        'cryptocurrency' => $transaction->cryptocurrency ? [
                            'name' => $transaction->cryptocurrency->name,
                            'symbol' => $transaction->cryptocurrency->symbol
                        ] : null,
                        'profit_earned' => $transaction->profit_earned,
                        'is_withdrawn' => $transaction->is_withdrawn
                    ];
                    break;

                case 'flexible_deposit':
                case 'flexible_withdraw':
                case 'flexible_interest':
                    $data['details'] = [
                        'balance_after' => $transaction->balance_after
                    ];
                    break;
            }

            return $data;
        });

        // Get account summary
        $summary = [
            'current_balance' => $user->balance,
            'total_deposits' => DepositRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->sum('approved_amount'),
            'total_withdrawals' => WithdrawalRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->sum('amount'),
            'active_investments' => [
                'crypto' => CryptoInvestment::where('user_id', $user->id)
                    ->where('is_withdrawn', false)
                    ->sum('amount'),
                'flexible' => FlexibleInvestmentTransaction::whereHas('investment', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->where('type', 'deposit')->sum('amount')
            ]
        ];

        return response()->json([
            'summary' => $summary,
            'transactions' => $formattedTransactions,
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total()
            ]
        ]);
    }
}