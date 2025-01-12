<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        

        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_user_id')->constrained('users')->onDelete('cascade');
            $table->string('investment_type'); // 'crypto' or 'flexible'
            $table->foreignId('investment_id'); // ID of the investment
            $table->decimal('reward_amount', 10, 2);
            $table->timestamps();

            // Ensure one reward per investment

            $table->unique(
                    ['referred_user_id', 'investment_type', 'investment_id'],
                    'referral_rewards_unique'
                );

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_rewards');
       
    }
};