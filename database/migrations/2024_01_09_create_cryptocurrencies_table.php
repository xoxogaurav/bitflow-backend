<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cryptocurrencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol');
            $table->string('icon_url');
            $table->integer('investment_duration_days');
            $table->decimal('minimum_deposit', 10, 2);
            $table->decimal('maximum_deposit', 10, 2);
            $table->decimal('profit_percentage', 5, 2);
            $table->string('wallet_address');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cryptocurrencies');
    }
};