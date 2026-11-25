<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commercial_invitations', function (Blueprint $table): void {
            $table->string('role', 40)->nullable()->after('user_id');
            $table->string('delivery_status', 20)->default('sent')->after('accepted_at');
            $table->dateTime('sent_at')->nullable()->change();
        });

        DB::table('commercial_invitations')
            ->select(['id', 'user_id'])
            ->orderBy('id')
            ->chunkById(100, function ($invitations): void {
                foreach ($invitations as $invitation) {
                    $role = DB::table('users')
                        ->where('id', $invitation->user_id)
                        ->value('role');

                    if ($role !== null) {
                        DB::table('commercial_invitations')
                            ->where('id', $invitation->id)
                            ->update(['role' => $role]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('commercial_invitations', function (Blueprint $table): void {
            $table->dropColumn(['role', 'delivery_status']);
            // sent_at reste nullable pour préserver les invitations en attente
            // ou en échec créées après cette migration.
        });
    }
};
