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
        Schema::create('doctor_payment', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('doctors')->onDelete('cascade');
            $table->unsignedBigInteger('session_id')->nullable()->default(null);
            $table->foreign('session_id')->references('session_id')->on('sessions')->onDelete('cascade');
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->foreign('payment_id')->references('payment_id')->on('payments')->onDelete('cascade');
            $table->string('bank_account_number')->nullable();
            $table->string('card_number')->nullable();
            $table->enum('doctor_request_status',['sent','accepted','canceled','rejected']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_payment');
    }
};
