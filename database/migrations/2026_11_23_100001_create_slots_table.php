<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('space_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->unique();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('price_excl_tax', 10, 2);

            // available | option | reserved | paid (voir App\Enums\SlotStatus)
            $table->string('status', 20)->default('available')->index();

            // Un seul titulaire par créneau : soit un exposant en base, soit un nom libre.
            $table->foreignId('exhibitor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('exhibitor_name')->nullable();

            $table->string('title')->nullable();
            $table->text('summary')->nullable();
            $table->text('speakers')->nullable();
            $table->string('support_path')->nullable();
            $table->string('support_original_name')->nullable();
            $table->timestamp('support_uploaded_at')->nullable();
            $table->text('notes')->nullable();

            // Évite d'envoyer deux fois le même rappel.
            $table->timestamp('support_reminder_sent_at')->nullable();
            $table->timestamp('eve_reminder_sent_at')->nullable();

            $table->timestamps();

            // Empêche deux créneaux au même moment dans le même espace.
            $table->unique(['space_id', 'date', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slots');
    }
};
