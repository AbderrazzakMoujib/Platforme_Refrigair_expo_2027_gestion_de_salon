<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_request_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('equipment_request_id')
                ->constrained('equipment_requests')
                ->cascadeOnDelete();

            $table->foreignId('equipment_id')
                ->constrained('equipment')
                ->restrictOnDelete();

            $table->string('equipment_name');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 12, 2);

            $table->timestamps();

            $table->unique([
                'equipment_request_id',
                'equipment_id',
            ], 'request_equipment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_request_items');
    }
};
