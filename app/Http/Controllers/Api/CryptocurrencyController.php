<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cryptocurrency;
use Illuminate\Http\Request;

class CryptocurrencyController extends Controller
{
    public function index()
    {
        $cryptocurrencies = Cryptocurrency::where('is_active', true)->get();
        return response()->json($cryptocurrencies);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'icon_url' => 'required|url',
            'investment_duration_days' => 'required|integer|min:1',
            'minimum_deposit' => 'required|numeric|min:0',
            'maximum_deposit' => 'required|numeric|gt:minimum_deposit',
            'profit_percentage' => 'required|numeric|between:0,100',
            'wallet_address' => 'required|string',
            'description' => 'nullable|string'
        ]);

        $crypto = Cryptocurrency::create($request->all());

        return response()->json([
            'message' => 'Cryptocurrency added successfully',
            'cryptocurrency' => $crypto
        ], 201);
    }

    public function show(Cryptocurrency $cryptocurrency)
    {
        return response()->json($cryptocurrency);
    }

    public function update(Request $request, Cryptocurrency $cryptocurrency)
    {
        $request->validate([
            'name' => 'string|max:255',
            'symbol' => 'string|max:10',
            'icon_url' => 'url',
            'investment_duration_days' => 'integer|min:1',
            'minimum_deposit' => 'numeric|min:0',
            'maximum_deposit' => 'numeric|gt:minimum_deposit',
            'profit_percentage' => 'numeric|between:0,100',
            'wallet_address' => 'string',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $cryptocurrency->update($request->all());

        return response()->json([
            'message' => 'Cryptocurrency updated successfully',
            'cryptocurrency' => $cryptocurrency
        ]);
    }

    public function destroy(Cryptocurrency $cryptocurrency)
    {
        $cryptocurrency->delete();
        return response()->json([
            'message' => 'Cryptocurrency deleted successfully'
        ]);
    }

    // List all cryptocurrencies for admin including inactive ones
    public function listAll()
    {
        $cryptocurrencies = Cryptocurrency::all();
        return response()->json($cryptocurrencies);
    }
}