<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exhibitor_catalog_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exhibitor_id')->unique()->constrained('exhibitors')->restrictOnDelete();
            $table->text('company_description')->nullable();
            $table->string('website', 255)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('status', 16)->default('draft')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exhibitor_catalog_profiles');
    }
};
