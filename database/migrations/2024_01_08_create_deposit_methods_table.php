<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposit_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');  // crypto, upi, bank
            $table->boolean('is_active')->default(true);
            $table->json('details');  // Store address, UPI ID, QR code URL etc
            $table->text('instructions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_methods');
    }
};