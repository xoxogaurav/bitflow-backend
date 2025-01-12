<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:upi,crypto',
            'withdrawal_details' => 'required|array',
            'withdrawal_details.address' => 'required_if:type,crypto',
            'withdrawal_details.upi_id' => 'required_if:type,upi'
        ]);

        if ($request->amount > $request->user()->balance) {
            return response()->json([
                'message' => 'Insufficient balance'
            ], 400);
        }

        try {
            DB::transaction(function () use ($request) {
                // Create withdrawal request
                $withdrawalRequest = WithdrawalRequest::create([
                    'user_id' => $request->user()->id,
                    'amount' => $request->amount,
                    'type' => $request->type,
                    'withdrawal_details' => $request->withdrawal_details
                ]);

                // Deduct balance immediately
                $user = $request->user();
                $user->balance -= $request->amount;
                $user->save();

            });

            return response()->json([
                'message' => 'Withdrawal request created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating withdrawal request'
            ], 500);
        }
    }

    public function processRequest(Request $request, WithdrawalRequest $withdrawalRequest)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string',
            'transaction_id' => 'required_if:status,approved|string|nullable'
        ]);

        try {
            DB::transaction(function () use ($request, $withdrawalRequest) {
                // If rejected, refund the amount
                if ($request->status === 'rejected') {
                    $user = $withdrawalRequest->user;
                    $user->balance += $withdrawalRequest->amount;
                    $user->save();
                }

                $withdrawalRequest->update([
                    'status' => $request->status,
                    'admin_note' => $request->admin_note,
                    'transaction_id' => $request->transaction_id
                ]);
            });

            return response()->json([
                'message' => 'Withdrawal request processed successfully',
                'withdrawal' => $withdrawalRequest->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $withdrawals = WithdrawalRequest::with('user')->latest()->get();
        return response()->json($withdrawals);
    }

    public function userWithdrawals(Request $request)
    {
        $withdrawals = WithdrawalRequest::where('user_id', $request->user()->id)
            ->latest()
            ->get();
        return response()->json($withdrawals);
    }
}