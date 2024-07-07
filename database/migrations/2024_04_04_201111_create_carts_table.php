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
        Schema::create('carts', function (Blueprint $table) {
            $table->id('cart_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('parents')->onDelete('cascade');
            $table->unsignedBigInteger('event_id')->nullable()->default(null);
            $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade');
            $table->double('total_amount');
            $table->integer('quantity')->nullable();
            $table->integer('number_of_tickets')->default(0);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
