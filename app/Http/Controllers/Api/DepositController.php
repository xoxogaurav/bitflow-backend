<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DepositRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    // User creates deposit request
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'transaction_id' => 'required|string|unique:deposit_requests'
        ]);

        $depositRequest = DepositRequest::create([
            'user_id' => $request->user()->id,
            'requested_amount' => $request->amount,
            'transaction_id' => $request->transaction_id,
        ]);

        return response()->json([
            'message' => 'Deposit request created successfully',
            'deposit' => $depositRequest
        ], 201);
    }

    // Admin approves/rejects deposit request
    public function processRequest(Request $request, DepositRequest $depositRequest)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'approved_amount' => 'required_if:status,approved|numeric|min:0',
            'admin_note' => 'nullable|string'
        ]);
        
        // Check if the request is already approved
        if ($depositRequest->status === 'approved') {
            return response()->json([
                'message' => 'This request has already been approved.'
            ], 400);
        }
    
        // Check if the approved amount is greater than the requested amount
        if ($request->status === 'approved' && $request->approved_amount > $depositRequest->requested_amount) {
            return response()->json([
                'message' => 'Approved amount cannot be greater than the requested amount.'
            ], 400);
        }

        try {
            DB::transaction(function () use ($request, $depositRequest) {
                $depositRequest->update([
                    'status' => $request->status,
                    'approved_amount' => $request->approved_amount,
                    'admin_note' => $request->admin_note
                ]);

                if ($request->status === 'approved') {
                    $user = $depositRequest->user;
                    $user->balance += $request->approved_amount;
                    $user->save();
                }
            });

            return response()->json([
                'message' => 'Deposit request processed successfully',
                'deposit' => $depositRequest->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error processing deposit request'
            ], 500);
        }
    }

    // List all deposit requests (admin)
    public function index()
    {
        $deposits = DepositRequest::with('user')->latest()->get();
        return response()->json($deposits);
    }

    // List user's deposit requests
    public function userDeposits(Request $request)
    {
        $deposits = DepositRequest::where('user_id', $request->user()->id)
            ->latest()
            ->get();
        return response()->json($deposits);
    }
}