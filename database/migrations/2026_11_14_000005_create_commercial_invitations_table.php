<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commercial_invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->dateTime('expires_at');
            $table->dateTime('sent_at');
            $table->dateTime('accepted_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'accepted_at', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_invitations');
    }
};
