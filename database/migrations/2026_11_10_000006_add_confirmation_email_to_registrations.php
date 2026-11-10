<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('confirmation_email_status')->nullable();
            $table->timestamp('confirmation_email_attempted_at')->nullable();
            $table->timestamp('confirmation_email_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['confirmation_email_status', 'confirmation_email_attempted_at', 'confirmation_email_sent_at']);
        });
    }
};
