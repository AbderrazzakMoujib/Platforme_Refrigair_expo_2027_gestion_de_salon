<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slot_request_decisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('slot_request_id')->nullable()->constrained('slot_requests')->nullOnDelete();
            $table->foreignId('exhibitor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('exhibitor_name');
            $table->string('request_title');
            $table->string('request_type', 20);
            $table->string('decision', 20)->index();
            $table->text('reason')->nullable();
            $table->foreignId('assigned_slot_id')->nullable()->constrained('slots')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('processor_name');
            $table->timestamp('processed_at')->index();
            $table->timestamps();
        });

        // Keep decisions made before this audit table was introduced.
        $existing = DB::table('slot_requests as requests')
            ->leftJoin('exhibitors', 'exhibitors.id', '=', 'requests.exhibitor_id')
            ->leftJoin('users as processors', 'processors.id', '=', 'requests.processed_by')
            ->whereIn('requests.status', ['accepted', 'rejected'])
            ->select([
                'requests.id as request_id',
                'requests.exhibitor_id',
                'exhibitors.company_name as exhibitor_name',
                'requests.title',
                'requests.type',
                'requests.status',
                'requests.rejection_reason',
                'requests.assigned_slot_id',
                'requests.processed_by',
                'processors.name as processor_name',
                'requests.processed_at',
                'requests.updated_at',
            ])->get();

        foreach ($existing as $decision) {
            DB::table('slot_request_decisions')->insert([
                'slot_request_id' => $decision->request_id,
                'exhibitor_id' => $decision->exhibitor_id,
                'exhibitor_name' => $decision->exhibitor_name ?? 'Exposant supprimé',
                'request_title' => $decision->title,
                'request_type' => $decision->type,
                'decision' => $decision->status,
                'reason' => $decision->rejection_reason,
                'assigned_slot_id' => $decision->assigned_slot_id,
                'processed_by' => $decision->processed_by,
                'processor_name' => $decision->processor_name ?? 'Compte responsable indisponible',
                'processed_at' => $decision->processed_at ?? $decision->updated_at,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('slot_request_decisions');
    }
};
