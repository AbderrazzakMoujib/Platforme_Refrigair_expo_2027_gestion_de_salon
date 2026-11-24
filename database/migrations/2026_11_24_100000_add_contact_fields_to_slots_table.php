<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Coordonnées d'un titulaire qui n'est pas exposant (réservation saisie par le commercial).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slots', function (Blueprint $table): void {
            $table->string('contact_name')->nullable()->after('exhibitor_name');
            $table->string('contact_email')->nullable()->after('contact_name');
            $table->string('contact_phone', 40)->nullable()->after('contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('slots', function (Blueprint $table): void {
            $table->dropColumn(['contact_name', 'contact_email', 'contact_phone']);
        });
    }
};
