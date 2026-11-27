<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salon_plans', function (Blueprint $table): void {
            // Row id 1 is the single shared plan for all Commercials and Exhibitors.
            $table->unsignedTinyInteger('id')->primary();
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('salon_plans')->insert([
            'id' => 1,
            'file_path' => null,
            'original_name' => null,
            'updated_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('salon_plans');
    }
};
