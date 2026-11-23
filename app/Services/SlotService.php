<?php

namespace App\Services;

use App\Enums\SlotRequestStatus;
use App\Enums\SlotStatus;
use App\Enums\SpaceType;
use App\Models\Exhibitor;
use App\Models\Slot;
use App\Models\SlotRequest;
use App\Models\User;
use App\Notifications\SlotConfirmedNotification;
use App\Notifications\SlotRequestRejectedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Règles métier des créneaux : attribution sans double réservation,
 * traitement des demandes et indicateurs de chiffre d'affaires.
 */
class SlotService
{
    /**
     * Met à jour un créneau depuis le planning.
     *
     * Le titulaire est soit un exposant inscrit (exhibitor_id), soit une
     * personne / société non exposante (exhibitor_name + coordonnées).
     *
     * Un créneau déjà pris ne peut pas être attribué à un autre titulaire :
     * il faut d'abord le repasser en « disponible ».
     *
     * @param  array{status: string, exhibitor_id?: ?int, exhibitor_name?: ?string, contact_name?: ?string, contact_email?: ?string, contact_phone?: ?string, title?: ?string, summary?: ?string, speakers?: ?string, notes?: ?string}  $data
     */
    public function update(Slot $slot, array $data): Slot
    {
        $wasConfirmed = false;

        $slot = DB::transaction(function () use ($slot, $data, &$wasConfirmed): Slot {
            /** @var Slot $locked */
            $locked = Slot::query()->lockForUpdate()->findOrFail($slot->id);

            $status = SlotStatus::from($data['status']);
            $exhibitorId = ! empty($data['exhibitor_id']) ? (int) $data['exhibitor_id'] : null;
            $exhibitorName = $exhibitorId ? null : (trim((string) ($data['exhibitor_name'] ?? '')) ?: null);

            if ($status === SlotStatus::Available) {
                $exhibitorId = null;
                $exhibitorName = null;
            } elseif ($locked->status->isTaken() && ! $this->sameHolder($locked, $exhibitorId, $exhibitorName)) {
                throw ValidationException::withMessages([
                    'exhibitor_id' => "Le créneau {$locked->code} est déjà attribué à « {$locked->holder_name} ». Libérez-le avant de l'attribuer à quelqu'un d'autre.",
                ]);
            }

            // Coordonnées : uniquement pour un titulaire non exposant.
            $external = $exhibitorName !== null;
            $contact = [
                'contact_name' => $external ? (trim((string) ($data['contact_name'] ?? '')) ?: null) : null,
                'contact_email' => $external ? (trim((string) ($data['contact_email'] ?? '')) ?: null) : null,
                'contact_phone' => $external ? (trim((string) ($data['contact_phone'] ?? '')) ?: null) : null,
            ];

            $canBeNotified = $exhibitorId !== null || $contact['contact_email'] !== null;

            $wasConfirmed = $status->isSold()
                && $canBeNotified
                && (! $locked->status->isSold() || ! $this->sameHolder($locked, $exhibitorId, $exhibitorName));

            // Créneau libéré ou nouveau titulaire : le support et les rappels
            // de l'ancien titulaire ne doivent pas lui être transmis.
            if ($status === SlotStatus::Available || ! $this->sameHolder($locked, $exhibitorId, $exhibitorName)) {
                $this->resetHolderData($locked);
            }

            $locked->update($contact + [
                'status' => $status,
                'exhibitor_id' => $exhibitorId,
                'exhibitor_name' => $exhibitorName,
                'title' => $data['title'] ?? null,
                'summary' => $data['summary'] ?? null,
                'speakers' => $data['speakers'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            return $locked;
        });

        if ($wasConfirmed) {
            $this->notifyHolder($slot, new SlotConfirmedNotification($slot));
        }

        return $slot;
    }

    /** Accepte une demande et attribue l'un des deux créneaux choisis. */
    public function accept(SlotRequest $request, int $slotId, User $processor): Slot
    {
        $slot = DB::transaction(function () use ($request, $slotId, $processor): Slot {
            /** @var SlotRequest $lockedRequest */
            $lockedRequest = SlotRequest::query()->lockForUpdate()->findOrFail($request->id);

            if ($lockedRequest->status !== SlotRequestStatus::Pending) {
                throw ValidationException::withMessages(['slot_id' => 'Cette demande a déjà été traitée.']);
            }

            if (! in_array($slotId, $lockedRequest->choiceIds(), true)) {
                throw ValidationException::withMessages(['slot_id' => 'Le créneau doit faire partie des choix de l\'exposant.']);
            }

            /** @var Slot $slot */
            $slot = Slot::query()->lockForUpdate()->findOrFail($slotId);

            if ($slot->status->isTaken()) {
                throw ValidationException::withMessages([
                    'slot_id' => "Le créneau {$slot->code} n'est plus disponible.",
                ]);
            }

            $this->resetHolderData($slot);

            $slot->update([
                'status' => SlotStatus::Reserved,
                'exhibitor_id' => $lockedRequest->exhibitor_id,
                'exhibitor_name' => null,
                'contact_name' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'title' => $lockedRequest->title,
                'summary' => $lockedRequest->summary,
                'speakers' => $lockedRequest->speakers,
            ]);

            $lockedRequest->update([
                'status' => SlotRequestStatus::Accepted,
                'assigned_slot_id' => $slot->id,
                'rejection_reason' => null,
                'processed_by' => $processor->id,
                'processed_at' => now(),
            ]);

            return $slot;
        });

        $this->notifyHolder($slot, new SlotConfirmedNotification($slot));

        return $slot;
    }

    public function reject(SlotRequest $request, string $reason, User $processor): void
    {
        abort_unless($request->status === SlotRequestStatus::Pending, 422, 'Cette demande a déjà été traitée.');

        $request->update([
            'status' => SlotRequestStatus::Rejected,
            'rejection_reason' => $reason,
            'processed_by' => $processor->id,
            'processed_at' => now(),
        ]);

        $exhibitor = $request->exhibitor;

        if ($exhibitor) {
            $this->notifyExhibitor($exhibitor, new SlotRequestRejectedNotification($request));
        }
    }

    /**
     * Indicateurs du tableau de bord commercial.
     *
     * - vendus : réservé + payé (l'option est exclue) ;
     * - CA HT : prix des créneaux vendus ;
     * - CA encaissé : prix des créneaux payés.
     */
    public function stats(): array
    {
        $slots = Slot::query()->with('space:id,type')->get();

        $bySpace = collect(SpaceType::cases())->mapWithKeys(function (SpaceType $type) use ($slots): array {
            $spaceSlots = $slots->filter(fn (Slot $slot) => $slot->space?->type === $type);

            return [$type->value => [
                'label' => $type->label(),
            ] + $this->summarize($spaceSlots)];
        });

        $byDay = $slots
            ->groupBy(fn (Slot $slot) => $slot->date->toDateString())
            ->sortKeys()
            ->map(fn (Collection $daySlots, string $date): array => [
                'date' => $daySlots->first()->date,
            ] + $this->summarize($daySlots))
            ->values();

        return [
            'total' => $this->summarize($slots),
            'spaces' => $bySpace->all(),
            'days' => $byDay->all(),
        ];
    }

    private function summarize(Collection $slots): array
    {
        $sold = $slots->filter(fn (Slot $slot) => $slot->status->isSold());
        $paid = $slots->filter(fn (Slot $slot) => $slot->status === SlotStatus::Paid);
        $total = $slots->count();

        return [
            'total' => $total,
            'sold' => $sold->count(),
            'options' => $slots->filter(fn (Slot $slot) => $slot->status === SlotStatus::Option)->count(),
            'available' => $slots->filter(fn (Slot $slot) => $slot->status === SlotStatus::Available)->count(),
            'revenue' => (float) $sold->sum(fn (Slot $slot) => (float) $slot->price_excl_tax),
            'collected' => (float) $paid->sum(fn (Slot $slot) => (float) $slot->price_excl_tax),
            'fill_rate' => $total > 0 ? round($sold->count() / $total * 100, 1) : 0.0,
        ];
    }

    /** Efface le support (fichier compris) et l'historique des rappels du titulaire précédent. */
    private function resetHolderData(Slot $slot): void
    {
        if ($slot->support_path) {
            $path = $slot->support_path;
            // Suppression du fichier seulement une fois la transaction validée.
            DB::afterCommit(fn () => Storage::disk('local')->delete($path));
        }

        $slot->forceFill([
            'support_path' => null,
            'support_original_name' => null,
            'support_uploaded_at' => null,
            'support_reminder_sent_at' => null,
            'eve_reminder_sent_at' => null,
        ]);
    }

    private function sameHolder(Slot $slot, ?int $exhibitorId, ?string $exhibitorName): bool
    {
        if ($slot->exhibitor_id !== null || $exhibitorId !== null) {
            return (int) $slot->exhibitor_id === (int) $exhibitorId;
        }

        return mb_strtolower(trim((string) $slot->exhibitor_name)) === mb_strtolower(trim((string) $exhibitorName));
    }

    /**
     * Prévient le titulaire d'un créneau :
     * exposant inscrit (compte ou e-mail de sa fiche), sinon e-mail du contact externe.
     */
    public function notifyHolder(Slot $slot, Notification $notification): bool
    {
        $exhibitor = $slot->exhibitor()->with('user')->first();

        if ($exhibitor) {
            return $this->notifyExhibitor($exhibitor, $notification);
        }

        if ($slot->contact_email) {
            NotificationFacade::route('mail', $slot->contact_email)->notify($notification);

            return true;
        }

        return false;
    }

    /** Enregistre le support de présentation (remplace l'éventuel précédent). */
    public function storeSupport(Slot $slot, UploadedFile $file): Slot
    {
        $previous = $slot->support_path;

        $path = $file->storeAs(
            'slot-supports',
            $slot->code.'-'.now()->format('YmdHis').'.'.strtolower($file->getClientOriginalExtension()),
            'local'
        );

        $slot->update([
            'support_path' => $path,
            'support_original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'support_uploaded_at' => now(),
        ]);

        if ($previous && $previous !== $path) {
            Storage::disk('local')->delete($previous);
        }

        return $slot;
    }

    /**
     * Compte exposant actif : notification (base + e‑mail).
     * Sans compte : e‑mail seul, à l'adresse de la fiche exposant.
     */
    private function notifyExhibitor(Exhibitor $exhibitor, Notification $notification): bool
    {
        $user = $exhibitor->user;

        if ($user && $user->is_active) {
            $user->notify($notification);

            return true;
        }

        if ($exhibitor->email) {
            NotificationFacade::route('mail', $exhibitor->email)->notify($notification);

            return true;
        }

        return false;
    }
}
