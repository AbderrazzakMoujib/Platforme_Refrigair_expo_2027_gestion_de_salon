<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slot_requests', function (Blueprint $table) {
            $table->text('support_details')->nullable();
            $table->string('support_review_status', 30)
                ->default('not_submitted');
            $table->text('support_review_note')->nullable();
            $table->timestamp('support_reviewed_at')->nullable();
            $table->foreignId('support_reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::create('conference_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('slot_request_id')
                ->constrained('slot_requests')
                ->cascadeOnDelete();

            $table->foreignId('sender_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['slot_request_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conference_messages');

        Schema::table('slot_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('support_reviewed_by');

            $table->dropColumn([
                'support_details',
                'support_review_status',
                'support_review_note',
                'support_reviewed_at',
            ]);
        });
    }
};
