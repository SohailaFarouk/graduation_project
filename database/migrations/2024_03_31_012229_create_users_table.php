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
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->date('date_of_birth');
            $table->text('address');
            $table->string('nat_id', 14)->unique();
            $table->string('phone_number', 11);
            $table->string('image')->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->enum('marital_status', ['married', 'single' ,'widowed','divorced'])->default('single');
            $table->enum('role',['parent','admin','doctor'])->default('parent');
            $table->string('token')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
