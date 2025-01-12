<?php

namespace App\Services;

use App\Models\User;
use App\Models\ReferralReward;
use Illuminate\Support\Facades\DB;

class ReferralService
{
    const REWARD_AMOUNT = 100.00; // INR

    public function processReferralReward(User $user, string $investmentType, int $investmentId)
    {
        // Check if user was referred
        if (!$user->referred_by) {
            return;
        }

        // Check if reward already given for this investment
        $existingReward = ReferralReward::where([
            'referred_user_id' => $user->id,
            'investment_type' => $investmentType,
            'investment_id' => $investmentId
        ])->exists();

        if ($existingReward) {
            return;
        }

        try {
            DB::transaction(function () use ($user, $investmentType, $investmentId) {
                // Create reward record
                ReferralReward::create([
                    'referrer_id' => $user->referred_by,
                    'referred_user_id' => $user->id,
                    'investment_type' => $investmentType,
                    'investment_id' => $investmentId,
                    'reward_amount' => self::REWARD_AMOUNT
                ]);

                // Add reward amount to referrer's balance
                $referrer = User::find($user->referred_by);
                $referrer->balance += self::REWARD_AMOUNT;
                $referrer->save();
            });
        } catch (\Exception $e) {
            \Log::error('Error processing referral reward: ' . $e->getMessage());
        }
    }
}