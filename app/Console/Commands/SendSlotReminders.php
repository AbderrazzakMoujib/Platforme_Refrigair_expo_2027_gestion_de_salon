<?php

namespace App\Console\Commands;

use App\Enums\SlotStatus;
use App\Models\Slot;
use App\Notifications\SlotEveReminderNotification;
use App\Notifications\SlotSupportReminderNotification;
use App\Services\SlotService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendSlotReminders extends Command
{
    protected $signature = 'slots:send-reminders';

    protected $description = 'Envoie les rappels supports et les rappels des intervenants';

    public function handle(SlotService $service): int
    {
        $today = now()->startOfDay();

        $eventStart = Carbon::parse('2027-02-02')->startOfDay();

        $supportDeadline = $eventStart->copy()->subDays(7);

        $sold = [
            SlotStatus::Reserved->value,
            SlotStatus::Paid->value,
        ];

        $reachable = fn ($query) => $query
            ->whereNotNull('exhibitor_id')
            ->orWhereNotNull('contact_email');

        $supportCount = 0;

        if (
            $today->greaterThanOrEqualTo($supportDeadline)
            && $today->lessThan($eventStart)
        ) {
            $supportSlots = Slot::query()
                ->whereIn('status', $sold)
                ->where($reachable)
                ->whereNull('support_path')
                ->whereNull('support_reminder_sent_at')
                ->whereDate('date', '>=', $eventStart->toDateString())
                ->get();

            foreach ($supportSlots as $slot) {
                if ($service->notifyHolder(
                    $slot,
                    new SlotSupportReminderNotification($slot)
                )) {
                    $slot->update([
                        'support_reminder_sent_at' => now(),
                    ]);

                    $supportCount++;
                }
            }
        }

        $tomorrow = $today->copy()->addDay()->toDateString();

        $eveSlots = Slot::query()
            ->whereIn('status', $sold)
            ->where($reachable)
            ->whereNull('eve_reminder_sent_at')
            ->whereDate('date', $tomorrow)
            ->get();

        $eveCount = 0;

        foreach ($eveSlots as $slot) {
            if ($service->notifyHolder(
                $slot,
                new SlotEveReminderNotification($slot)
            )) {
                $slot->update([
                    'eve_reminder_sent_at' => now(),
                ]);

                $eveCount++;
            }
        }

        $this->info("Rappels support envoyés : {$supportCount}");
        $this->info("Rappels veille envoyés : {$eveCount}");

        return self::SUCCESS;
    }
}
