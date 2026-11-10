<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('buyer_name');
            $table->text('buyer_email');
            $table->text('buyer_phone');
            $table->text('participant_name');
            $table->text('participant_birth_date');
            $table->string('status')->default('pending');
            $table->unsignedInteger('amount');
            $table->string('currency', 3);
            $table->string('stripe_session_id')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('checkout_expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
