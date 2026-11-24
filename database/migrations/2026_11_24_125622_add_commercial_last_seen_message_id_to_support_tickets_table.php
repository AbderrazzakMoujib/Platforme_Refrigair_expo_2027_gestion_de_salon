<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('commercial_last_seen_message_id')->nullable();
        });

        // Ma n7esbouch anciens messages comme non lus.
        DB::table('support_tickets')->update([
            'commercial_last_seen_message_id' => DB::raw(
                '(SELECT MAX(id) FROM support_messages WHERE support_ticket_id = support_tickets.id)'
            ),
        ]);
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn('commercial_last_seen_message_id');
        });
    }
};