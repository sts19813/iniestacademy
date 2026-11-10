<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('username', 64)->unique();
            $table->string('password_hash');
            $table->text('totp_secret');
            $table->bigInteger('last_totp_step')->nullable();
            $table->unsignedInteger('session_version')->default(1);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('site_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        Schema::create('admin_audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_account_id')->nullable()->constrained('admin_accounts');
            $table->string('event', 80);
            $table->text('metadata')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_events');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('admin_accounts');
    }
};
