<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mail_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestampTz('occurred_at')->index();
            $table->enum('direction', ['inbound', 'outbound', 'local'])->index();
            $table->string('event_type')->index();
            $table->string('exim_message_id')->nullable()->index();
            $table->string('sender')->nullable()->index();
            $table->string('recipient')->nullable()->index();
            $table->string('domain')->index();
            $table->string('whm_account')->index();
            $table->ipAddress('ip')->nullable()->index();
            $table->string('remote_mta')->nullable();
            $table->string('smtp_code')->nullable();
            $table->string('smtp_response')->nullable();
            $table->string('status')->nullable()->index();
            $table->string('error_category')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamps();
        });

        DB::statement('CREATE UNIQUE INDEX mail_events_dedupe_unique ON mail_events (exim_message_id, event_type, occurred_at, recipient) WHERE exim_message_id IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS mail_events_dedupe_unique');
        Schema::dropIfExists('mail_events');
    }
};
