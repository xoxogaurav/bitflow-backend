<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DepositMethod;
use Illuminate\Http\Request;

class DepositMethodController extends Controller
{
    public function index()
    {
        $methods = DepositMethod::where('is_active', true)->get();
        return response()->json($methods);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:crypto,upi,bank',
            'details' => 'required|array',
            'instructions' => 'nullable|string'
        ]);

        $method = DepositMethod::create($request->all());

        return response()->json([
            'message' => 'Deposit method created successfully',
            'method' => $method
        ], 201);
    }

    public function update(Request $request, DepositMethod $method)
    {
        $request->validate([
            'name' => 'string',
            'type' => 'in:crypto,upi,bank',
            'details' => 'array',
            'instructions' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $method->update($request->all());

        return response()->json([
            'message' => 'Deposit method updated successfully',
            'method' => $method
        ]);
    }

    public function destroy(DepositMethod $method)
    {
        $method->delete();
        return response()->json([
            'message' => 'Deposit method deleted successfully'
        ]);
    }
}