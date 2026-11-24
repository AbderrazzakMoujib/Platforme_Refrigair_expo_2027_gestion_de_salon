<?php

namespace App\Console\Commands;

use App\Enums\SlotStatus;
use App\Models\Slot;
use App\Notifications\SlotEveReminderNotification;
use App\Notifications\SlotSupportReminderNotification;
use App\Services\SlotService;
use Illuminate\Console\Command;

/**
 * Rappels automatiques des créneaux vendus (réservés ou payés) :
 * - J‑7 (ou moins si la commande a été manquée) : support pas encore envoyé ;
 * - la veille : arrivée 15 minutes avant pour le test technique.
 *
 * Chaque rappel n'est envoyé qu'une fois (colonnes *_sent_at).
 */
class SendSlotReminders extends Command
{
    protected $signature = 'slots:send-reminders';

    protected $description = 'Envoie les rappels J-7 (support) et J-1 (test technique) des créneaux de conférence et du lounge';

    public function handle(SlotService $service): int
    {
        $today = now()->startOfDay();
        $sold = [SlotStatus::Reserved->value, SlotStatus::Paid->value];

        // Titulaire joignable : exposant inscrit, ou personne externe avec un e-mail.
        $reachable = fn ($query) => $query->whereNotNull('exhibitor_id')->orWhereNotNull('contact_email');

        $supportReminders = Slot::query()
            ->whereIn('status', $sold)
            ->where($reachable)
            ->whereNull('support_path')
            ->whereNull('support_reminder_sent_at')
            ->whereDate('date', '>', $today->toDateString())
            ->whereDate('date', '<=', $today->copy()->addDays(7)->toDateString())
            ->get();

        foreach ($supportReminders as $slot) {
            $service->notifyHolder($slot, new SlotSupportReminderNotification($slot));
            $slot->update(['support_reminder_sent_at' => now()]);
        }

        $eveReminders = Slot::query()
            ->whereIn('status', $sold)
            ->where($reachable)
            ->whereNull('eve_reminder_sent_at')
            ->whereDate('date', $today->copy()->addDay()->toDateString())
            ->get();

        foreach ($eveReminders as $slot) {
            $service->notifyHolder($slot, new SlotEveReminderNotification($slot));
            $slot->update(['eve_reminder_sent_at' => now()]);
        }

        $this->info("Rappels support envoyés : {$supportReminders->count()}");
        $this->info("Rappels veille envoyés : {$eveReminders->count()}");

        return self::SUCCESS;
    }
}
