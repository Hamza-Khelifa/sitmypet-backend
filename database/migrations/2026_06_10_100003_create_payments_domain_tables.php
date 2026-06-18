<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('mangopay_user_id')->unique();
            $table->string('mangopay_wallet_id')->unique();
            $table->string('mangopay_bank_account_id')->nullable();
            $table->string('kyc_status')->default('created');
            $table->string('currency')->default('EUR');
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wallet_id');
            $table->string('mangopay_transaction_id')->unique();
            $table->string('type'); // pay_in, transfer, pay_out, refund
            $table->string('status'); // pending, succeeded, failed
            $table->decimal('amount', 10, 2);
            $table->decimal('fees', 10, 2)->default(0.00);
            $table->string('currency')->default('EUR');
            $table->uuid('reference_id')->nullable(); // Can link to bookings or other transactions
            $table->timestamps();

            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('wallets');
    }
};
