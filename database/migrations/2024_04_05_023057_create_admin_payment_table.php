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
        Schema::create('admin_payment', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('admins')->onDelete('cascade');
            $table->unsignedBigInteger('payment_id')->nullable()->default(null);
            $table->foreign('payment_id')->references('payment_id')->on('payments')->onDelete('cascade');
            $table->string('sales_report')->nullable();

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_payment');
    }
};
