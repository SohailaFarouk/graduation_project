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
        Schema::create('sessions', function (Blueprint $table) {
            $table->id('session_id');
            $table->unsignedBigInteger('user_id')->nullable()->default(null);
            $table->foreign('user_id')->references('user_id')->on('parents')->onDelete('cascade');
            $table->unsignedBigInteger('cart_id')->nullable()->default(null);
            $table->foreign('cart_id')->references('cart_id')->on('carts')->onDelete('cascade');
            $table->unsignedBigInteger('appointment_id')->nullable()->default(null);
            $table->foreign('appointment_id')->references('appointment_id')->on('appointments')->onDelete('cascade');
            $table->enum('session_type',['Therapy','Psychiatry','Physiatry','Prosthetics ']);
            $table->enum('status',['available','reserved','payment','canceled','finished'])->default('available');
            $table->double('session_fees')->nullable();
            $table->time('session_time')->nullable();
            $table->date('session_date')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
