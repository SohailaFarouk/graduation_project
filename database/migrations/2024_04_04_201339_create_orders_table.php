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
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->integer('order_number');
            $table->text('order_details');
            $table->double('order_amount');
            $table->unsignedBigInteger('cart_id')->nullable()->default(null);
            $table->foreign('cart_id')->references('cart_id')->on('carts')->onDelete('cascade');
            $table->unsignedBigInteger('voucher_id')->nullable()->default(null);
            $table->foreign('voucher_id')->references('voucher_id')->on('vouchers')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('parents')->onDelete('cascade');
            $table->enum('status',['preorder', 'processing', 'ordered', 'shpping', 'delivered' , 'finished','canceled' ,'returned']);
            $table->enum('payment_status' , ['paid' , 'unpaid'])->default('unpaid');
            $table->integer('quantity')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
