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
        Schema::create('auth_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestampTz('occurred_at')->index();
            $table->enum('proto', ['imap', 'pop3', 'smtp'])->index();
            $table->string('event_type')->index();
            $table->string('user_email')->nullable()->index();
            $table->string('domain')->index();
            $table->string('whm_account')->index();
            $table->ipAddress('ip')->nullable()->index();
            $table->enum('auth_result', ['success', 'fail'])->index();
            $table->string('failure_reason')->nullable()->index();
            $table->jsonb('meta')->nullable();
            $table->timestamps();
            $table->unique(['user_email', 'event_type', 'occurred_at', 'ip'], 'auth_events_dedupe_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_events');
    }
};
