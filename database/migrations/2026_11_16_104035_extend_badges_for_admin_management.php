<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->uuid('qr_token')->nullable()->unique();
            $table->string('status', 20)->default('active')->index();
            $table->softDeletes();
        });

        DB::table('badges')
            ->whereNull('qr_token')
            ->orderBy('id')
            ->chunkById(100, function ($badges): void {
                foreach ($badges as $badge) {
                    DB::table('badges')
                        ->where('id', $badge->id)
                        ->update([
                            'qr_token' => (string) Str::uuid(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropUnique(['qr_token']);
            $table->dropIndex(['status']);
            $table->dropColumn(['qr_token', 'status', 'deleted_at']);
        });
    }
};
