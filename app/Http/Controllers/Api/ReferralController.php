<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReferralReward;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function getReferralInfo(Request $request)
    {
        $user = $request->user();
        $referrals = $user->referrals()->with('referralRewards')->get();
        $rewards = $user->referralRewards()->with('referredUser')->latest()->get();

        $totalReferrals = $referrals->count();
        $totalRewards = $rewards->sum('reward_amount');
        $activeReferrals = $referrals->filter(function ($referral) {
            return $referral->referralRewards->isNotEmpty();
        })->count();

        return response()->json([
            'referral_code' => $user->referral_code,
            'referral_link' => config('app.url') . '/register?ref=' . $user->referral_code,
            'statistics' => [
                'total_referrals' => $totalReferrals,
                'active_referrals' => $activeReferrals,
                'total_rewards' => $totalRewards
            ],
            'recent_rewards' => $rewards->map(function ($reward) {
                return [
                    'amount' => $reward->reward_amount,
                    'date' => $reward->created_at,
                    'referred_user' => $reward->referredUser->name,
                    'investment_type' => $reward->investment_type
                ];
            })
        ]);
    }
}