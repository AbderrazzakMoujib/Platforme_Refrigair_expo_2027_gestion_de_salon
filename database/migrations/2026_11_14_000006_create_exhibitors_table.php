<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exhibitors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('commercial_id')->constrained('users')->restrictOnDelete();
            $table->string('company_name');
            $table->string('contact_name');
            $table->string('email')->index();
            $table->string('phone', 40)->nullable();
            $table->string('stand_number', 50)->nullable();
            $table->decimal('area_sqm', 8, 2)->nullable();
            $table->unsignedInteger('authorized_badges')->default(0);
            $table->timestamps();
            $table->index(['commercial_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exhibitors');
    }
};
