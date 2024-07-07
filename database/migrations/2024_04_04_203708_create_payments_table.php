<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->unsignedBigInteger('subscription_id')->default(1);
            $table->foreign('subscription_id')->references('subscription_id')->on('subscriptions')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('parents')->onDelete('cascade');
            $table->unsignedBigInteger('order_id')->nullable()->default(null);
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');
            $table->double('payment_amount');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
