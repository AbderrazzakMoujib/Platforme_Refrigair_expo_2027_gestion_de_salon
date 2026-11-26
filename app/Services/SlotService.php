<?php

namespace App\Services;

use App\Enums\SlotRequestStatus;
use App\Enums\SlotStatus;
use App\Enums\SpaceType;
use App\Enums\UserRole;
use App\Models\Exhibitor;
use App\Models\Slot;
use App\Models\SlotRequest;
use App\Models\SlotRequestDecision;
use App\Models\User;
use App\Notifications\SlotConfirmedNotification;
use App\Notifications\SlotRequestRejectedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

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
    public function update(Slot $slot, array $data, User $commercial): Slot
    {
        $wasConfirmed = false;

        $slot = DB::transaction(function () use ($slot, $data, $commercial, &$wasConfirmed): Slot {
            /** @var Slot $locked */
            $locked = Slot::query()->lockForUpdate()->findOrFail($slot->id);

            if ($commercial->role === UserRole::Commercial && ! $locked->status->isTaken()) {
                throw ValidationException::withMessages([
                    'slot_id' => 'La création de réservations est réservée au Responsable Conférences.',
                ]);
            }

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

            $slotCommercialId = match (true) {
                $status === SlotStatus::Available => null,
                $commercial->role === UserRole::Commercial => $commercial->id,
                $this->sameHolder($locked, $exhibitorId, $exhibitorName) => $locked->commercial_id,
                default => null,
            };

            $locked->update($contact + [
                'status' => $status,
                'exhibitor_id' => $exhibitorId,
                'commercial_id' => $slotCommercialId,
                'exhibitor_name' => $exhibitorName,
                'title' => $data['title'] ?? null,
                'summary' => $data['summary'] ?? null,
                'speakers' => $data['speakers'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            return $locked;
        });

        if ($wasConfirmed) {
            DB::afterCommit(fn () => $this->notifyHolder($slot, new SlotConfirmedNotification($slot)));
        }

        return $slot;
    }

    /** Reserve an available slot atomically; concurrent submissions cannot both claim it. */
    public function reserveAvailable(Slot $slot, array $data, User $processor): Slot
    {
        return DB::transaction(function () use ($slot, $data, $processor): Slot {
            $locked = Slot::query()->lockForUpdate()->findOrFail($slot->id);

            if ($locked->status !== SlotStatus::Available) {
                throw ValidationException::withMessages([
                    'slot_id' => "Le créneau {$locked->code} vient d'être réservé ou placé en option. Choisissez un autre créneau.",
                ]);
            }

            if (! SlotStatus::from($data['status'])->isTaken()) {
                throw ValidationException::withMessages([
                    'status' => 'Choisissez le statut Réservé, Payé ou Option pour créer une réservation.',
                ]);
            }

            return $this->update($locked, $data, $processor);
        });
    }

    /** Met à jour les informations métier d'une réservation créée par le responsable, sans déplacer le créneau. */
    public function updateManualReservation(Slot $slot, array $data, User $manager): Slot
    {
        return DB::transaction(function () use ($slot, $data, $manager): Slot {
            $locked = Slot::query()->lockForUpdate()->findOrFail($slot->id);
            Gate::forUser($manager)->authorize('manageManualReservation', $locked);

            $changes = [
                'contact_name' => $data['contact_name'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'title' => $data['title'] ?? null,
                'summary' => $data['summary'] ?? null,
                'speakers' => $data['speakers'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];

            // Le nom de société libre ne s'applique qu'aux réservations sans fiche Exposant.
            if ($locked->exhibitor_id === null) {
                $changes['exhibitor_name'] = trim((string) ($data['exhibitor_name'] ?? '')) ?: null;
            }

            $locked->update($changes);

            return $locked->fresh(['space', 'exhibitor']);
        });
    }

    /** Libère le créneau d'une réservation manuelle, sans supprimer le créneau du planning. */
    public function releaseManualReservation(Slot $slot, User $manager): Slot
    {
        return DB::transaction(function () use ($slot, $manager): Slot {
            $locked = Slot::query()->lockForUpdate()->findOrFail($slot->id);
            Gate::forUser($manager)->authorize('manageManualReservation', $locked);

            $this->resetHolderData($locked);
            $locked->update([
                'status' => SlotStatus::Available,
                'exhibitor_id' => null,
                'commercial_id' => null,
                'exhibitor_name' => null,
                'contact_name' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'title' => null,
                'summary' => null,
                'speakers' => null,
                'notes' => null,
            ]);

            return $locked->fresh(['space']);
        });
    }

    /** Delete non-accepted requests; accepted reservations are canceled and safely released with audit retained. */
    public function cancelRequest(SlotRequest $request, User $manager, ?string $reason = null): void
    {
        DB::transaction(function () use ($request, $manager, $reason): void {
            $lockedRequest = SlotRequest::query()->lockForUpdate()->findOrFail($request->id);
            Gate::forUser($manager)->authorize('cancel', $lockedRequest);

            $exhibitor = Exhibitor::query()->find($lockedRequest->exhibitor_id);
            $assignedSlotId = $lockedRequest->assigned_slot_id;

            if ($lockedRequest->status === SlotRequestStatus::Accepted && $assignedSlotId !== null) {
                $slot = Slot::query()->lockForUpdate()->find($assignedSlotId);

                if ($slot
                    && (int) $slot->exhibitor_id === (int) $lockedRequest->exhibitor_id
                    && $slot->status->isTaken()) {
                    $this->resetHolderData($slot);
                    $slot->update([
                        'status' => SlotStatus::Available,
                        'exhibitor_id' => null,
                        'commercial_id' => null,
                        'exhibitor_name' => null,
                        'contact_name' => null,
                        'contact_email' => null,
                        'contact_phone' => null,
                        'title' => null,
                        'summary' => null,
                        'speakers' => null,
                        'notes' => null,
                    ]);
                }
            }

            // Pending and rejected requests have no confirmed reservation to release.
            // Their conversations cascade with deletion; existing decision snapshots
            // survive because slot_request_decisions.slot_request_id is nullOnDelete.
            if ($lockedRequest->status !== SlotRequestStatus::Accepted) {
                $lockedRequest->delete();

                return;
            }

            $processedAt = now();
            $reason = trim((string) $reason);
            $lockedRequest->update([
                'status' => SlotRequestStatus::Cancelled,
                'rejection_reason' => $reason !== '' ? $reason : null,
                'processed_by' => $manager->id,
                'processed_at' => $processedAt,
            ]);

            SlotRequestDecision::query()->create([
                'slot_request_id' => $lockedRequest->id,
                'exhibitor_id' => $exhibitor?->id,
                'exhibitor_name' => $exhibitor?->company_name ?? 'Exposant indisponible',
                'request_title' => $lockedRequest->title,
                'request_type' => $lockedRequest->type->value,
                'decision' => SlotRequestStatus::Cancelled->value,
                'reason' => $reason !== '' ? $reason : 'Demande annulée par le Responsable Conférences.',
                'assigned_slot_id' => $assignedSlotId,
                'processed_by' => $manager->id,
                'processor_name' => $manager->name,
                'processed_at' => $processedAt,
            ]);
        });
    }

    /** Accepte une demande et attribue l'un des deux créneaux choisis. */
    public function accept(SlotRequest $request, int $slotId, User $processor): Slot
    {
        $slot = DB::transaction(function () use ($request, $slotId, $processor): Slot {
            /** @var SlotRequest $lockedRequest */
            $lockedRequest = SlotRequest::query()->lockForUpdate()->findOrFail($request->id);

            Gate::forUser($processor)->authorize('process', $lockedRequest);

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
            $exhibitor = Exhibitor::query()->findOrFail($lockedRequest->exhibitor_id);
            $processedAt = now();

            $slot->update([
                'status' => SlotStatus::Reserved,
                'exhibitor_id' => $lockedRequest->exhibitor_id,
                'commercial_id' => $exhibitor->commercial_id,
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
                'processed_at' => $processedAt,
            ]);

            SlotRequestDecision::query()->create([
                'slot_request_id' => $lockedRequest->id,
                'exhibitor_id' => $exhibitor->id,
                'exhibitor_name' => $exhibitor->company_name,
                'request_title' => $lockedRequest->title,
                'request_type' => $lockedRequest->type->value,
                'decision' => SlotRequestStatus::Accepted->value,
                'assigned_slot_id' => $slot->id,
                'processed_by' => $processor->id,
                'processor_name' => $processor->name,
                'processed_at' => $processedAt,
            ]);

            return $slot;
        });

        $this->notifyHolder($slot, new SlotConfirmedNotification($slot));

        return $slot;
    }

    public function reject(SlotRequest $request, string $reason, User $processor): void
    {
        abort_unless($request->status === SlotRequestStatus::Pending, 422, 'Cette demande a déjà été traitée.');

        $request = DB::transaction(function () use ($request, $reason, $processor): SlotRequest {
            /** @var SlotRequest $lockedRequest */
            $lockedRequest = SlotRequest::query()->lockForUpdate()->findOrFail($request->id);

            Gate::forUser($processor)->authorize('process', $lockedRequest);

            if ($lockedRequest->status !== SlotRequestStatus::Pending) {
                throw ValidationException::withMessages(['rejection_reason' => 'Cette demande a déjà été traitée.']);
            }

            $exhibitor = Exhibitor::query()->findOrFail($lockedRequest->exhibitor_id);
            $processedAt = now();

            $lockedRequest->update([
                'status' => SlotRequestStatus::Rejected,
                'rejection_reason' => $reason,
                'processed_by' => $processor->id,
                'processed_at' => $processedAt,
            ]);

            SlotRequestDecision::query()->create([
                'slot_request_id' => $lockedRequest->id,
                'exhibitor_id' => $exhibitor->id,
                'exhibitor_name' => $exhibitor->company_name,
                'request_title' => $lockedRequest->title,
                'request_type' => $lockedRequest->type->value,
                'decision' => SlotRequestStatus::Rejected->value,
                'reason' => $reason,
                'processed_by' => $processor->id,
                'processor_name' => $processor->name,
                'processed_at' => $processedAt,
            ]);

            return $lockedRequest;
        });

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

    /**
     * Upload legacy pour les créneaux qui ne sont pas gérés par un responsable.
     * Les protections sont réévaluées sous verrou pour empêcher un bypass HTTP.
     */
    public function storeSupport(Slot $slot, UploadedFile $file, User $actor): Slot
    {
        Gate::forUser($actor)->authorize('uploadSupport', $slot);

        $newPath = null;
        $previousPath = null;

        try {
            $updatedSlot = DB::transaction(function () use ($slot, $file, $actor, &$newPath, &$previousPath): Slot {
                $lockedSlot = Slot::query()->lockForUpdate()->findOrFail($slot->id);

                Gate::forUser($actor)->authorize('uploadSupport', $lockedSlot);

                $managedRequest = SlotRequest::query()
                    ->where('assigned_slot_id', $lockedSlot->id)
                    ->where('status', SlotRequestStatus::Accepted)
                    ->lockForUpdate()
                    ->first();

                abort_if(
                    $managedRequest,
                    403,
                    'Cette réservation doit utiliser le suivi Conférences & Lounge.'
                );

                $previousPath = $lockedSlot->support_path;
                $newPath = $this->storeSupportFile($lockedSlot, $file);

                if (! $lockedSlot->update([
                    'support_path' => $newPath,
                    'support_original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
                    'support_uploaded_at' => now(),
                ])) {
                    throw new RuntimeException('Le support n’a pas pu être enregistré.');
                }

                return $lockedSlot;
            });
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }

            throw $exception;
        }

        $this->deleteSupportIfUnreferenced($previousPath, $updatedSlot->id);

        return $updatedSlot;
    }

    /** Upload atomique du support et des métadonnées de collaboration. */
    public function uploadConferenceSupport(
        SlotRequest $request,
        UploadedFile $file,
        string $details,
        User $exhibitorUser
    ): SlotRequest {
        $newPath = null;
        $previousPath = null;
        $slotId = null;

        try {
            $updatedRequest = DB::transaction(function () use ($request, $file, $details, $exhibitorUser, &$newPath, &$previousPath, &$slotId): SlotRequest {
                $lockedRequest = SlotRequest::query()->lockForUpdate()->findOrFail($request->id);

                abort_unless(
                    $exhibitorUser->is_active
                        && $exhibitorUser->role === UserRole::Exposant
                        && $exhibitorUser->exhibitorProfile()->whereKey($lockedRequest->exhibitor_id)->exists(),
                    403
                );

                abort_unless(
                    $lockedRequest->status === SlotRequestStatus::Accepted
                        && $lockedRequest->assigned_slot_id !== null,
                    403,
                    'La réservation doit être confirmée avant l’envoi du support.'
                );

                $slot = Slot::query()->lockForUpdate()->findOrFail($lockedRequest->assigned_slot_id);
                abort_unless(
                    (int) $slot->exhibitor_id === (int) $lockedRequest->exhibitor_id
                        && $slot->status->isSold(),
                    403
                );

                abort_if(
                    $lockedRequest->support_review_status === 'approved',
                    403,
                    'Ce support est déjà approuvé. Le Responsable doit demander une révision avant tout remplacement.'
                );

                abort_unless(
                    in_array($lockedRequest->support_review_status, ['not_submitted', 'revision_requested'], true),
                    422,
                    'Un support est déjà en cours de vérification.'
                );

                $slotId = $slot->id;
                $previousPath = $slot->support_path;
                $newPath = $this->storeSupportFile($slot, $file);

                if (! $slot->update([
                    'support_path' => $newPath,
                    'support_original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
                    'support_uploaded_at' => now(),
                ])) {
                    throw new RuntimeException('Le fichier du support n’a pas pu être enregistré.');
                }

                if (! $lockedRequest->update([
                    'support_details' => $details,
                    'support_review_status' => 'pending',
                    'support_review_note' => null,
                    'support_reviewed_at' => null,
                    'support_reviewed_by' => null,
                ])) {
                    throw new RuntimeException('Les détails du support n’ont pas pu être enregistrés.');
                }

                return $lockedRequest->load(['exhibitor', 'assignedSlot']);
            });
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }

            throw $exception;
        }

        $this->deleteSupportIfUnreferenced($previousPath, $slotId);

        return $updatedRequest;
    }

    /** Applique une transition de revue sous verrou et vérifie le fichier stocké. */
    public function reviewConferenceSupport(
        SlotRequest $request,
        string $decision,
        ?string $note,
        User $manager
    ): SlotRequest {
        return DB::transaction(function () use ($request, $decision, $note, $manager): SlotRequest {
            $lockedRequest = SlotRequest::query()->lockForUpdate()->findOrFail($request->id);

            abort_unless(
                $manager->is_active && $manager->role === UserRole::ResponsableConferences,
                403
            );

            abort_unless(
                $lockedRequest->status === SlotRequestStatus::Accepted
                    && $lockedRequest->assigned_slot_id !== null,
                403
            );

            $slot = Slot::query()->lockForUpdate()->findOrFail($lockedRequest->assigned_slot_id);
            abort_unless(
                (int) $slot->exhibitor_id === (int) $lockedRequest->exhibitor_id
                    && $slot->status->isSold(),
                403
            );

            $path = $slot->support_path;
            $extension = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));
            abort_unless(
                $path
                    && in_array($extension, ['pdf', 'ppt', 'pptx'], true)
                    && Storage::disk('local')->exists($path),
                422,
                'Aucun fichier de support valide à vérifier.'
            );

            $currentStatus = $lockedRequest->support_review_status;
            $allowed = match ($decision) {
                'approved' => $currentStatus === 'pending',
                'revision_requested' => in_array($currentStatus, ['pending', 'approved'], true),
                default => false,
            };

            abort_unless($allowed, 422, 'Transition de statut non autorisée.');

            $reviewNote = $note === null ? null : trim($note);
            abort_if(
                $decision === 'revision_requested' && $reviewNote === '',
                422,
                'Une remarque est obligatoire pour demander une modification.'
            );

            $lockedRequest->update([
                'support_review_status' => $decision,
                'support_review_note' => $decision === 'revision_requested' ? $reviewNote : null,
                'support_reviewed_at' => now(),
                'support_reviewed_by' => $manager->id,
            ]);

            return $lockedRequest->load(['exhibitor', 'assignedSlot']);
        });
    }

    private function storeSupportFile(Slot $slot, UploadedFile $file): string
    {
        $extension = strtolower((string) $file->guessExtension());
        abort_unless(in_array($extension, ['pdf', 'ppt', 'pptx'], true), 422, 'Format de support non autorisé.');

        $path = $file->storeAs(
            'slot-supports',
            $slot->code.'-'.Str::uuid().'.'.$extension,
            'local'
        );

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Le support n’a pas pu être enregistré dans le stockage privé.');
        }

        return $path;
    }

    private function deleteSupportIfUnreferenced(?string $path, ?int $exceptSlotId): void
    {
        if (! $path) {
            return;
        }

        $isShared = Slot::query()
            ->where('support_path', $path)
            ->when($exceptSlotId, fn ($query) => $query->whereKeyNot($exceptSlotId))
            ->exists();

        if (! $isShared) {
            Storage::disk('local')->delete($path);
        }
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
