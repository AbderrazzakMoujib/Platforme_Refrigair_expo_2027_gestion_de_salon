<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slot_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exhibitor_id')->constrained()->cascadeOnDelete();

            // conference | lounge
            $table->string('type', 20);
            $table->string('title');
            $table->text('summary');
            $table->text('speakers');

            $table->foreignId('first_choice_slot_id')->nullable()->constrained('slots')->nullOnDelete();
            $table->foreignId('second_choice_slot_id')->nullable()->constrained('slots')->nullOnDelete();

            // pending | accepted | rejected (voir App\Enums\SlotRequestStatus)
            $table->string('status', 20)->default('pending')->index();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('assigned_slot_id')->nullable()->constrained('slots')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slot_requests');
    }
};
