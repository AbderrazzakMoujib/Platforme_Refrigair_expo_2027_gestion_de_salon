<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('badges', function (Blueprint $table): void { $table->id(); $table->foreignId('exhibitor_id')->constrained('exhibitors')->cascadeOnDelete(); $table->string('first_name'); $table->string('last_name'); $table->string('function'); $table->string('email'); $table->string('phone',40)->nullable(); $table->timestamps(); $table->index(['exhibitor_id','created_at']); }); }
    public function down(): void { Schema::dropIfExists('badges'); }
};
