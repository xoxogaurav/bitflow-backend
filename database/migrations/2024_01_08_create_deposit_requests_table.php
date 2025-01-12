<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('requested_amount', 10, 2);
            $table->decimal('approved_amount', 10, 2)->nullable();
            $table->string('transaction_id');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        // Add balance column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('balance', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_requests');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('balance');
        });
    }
};