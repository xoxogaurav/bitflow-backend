<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flexible_investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('total_invested', 15, 2)->default(0);
            $table->decimal('total_profit', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->timestamp('last_interest_calculation')->nullable();
            $table->timestamps();
        });

        Schema::create('flexible_investment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flexible_investment_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['deposit', 'withdraw', 'interest']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->timestamps();
        });

        Schema::create('flexible_investment_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('minimum_deposit', 10, 2);
            $table->decimal('maximum_deposit', 10, 2);
            $table->decimal('daily_profit_percentage', 5, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flexible_investment_transactions');
        Schema::dropIfExists('flexible_investments');
        Schema::dropIfExists('flexible_investment_settings');
    }
};