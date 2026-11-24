<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spaces', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            // conference | lounge (voir App\Enums\SpaceType)
            $table->string('type', 20)->unique();
            $table->unsignedInteger('capacity')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};
