<?php

namespace App\Http\Requests\Slots;

use App\Enums\SlotRequestStatus;
use App\Enums\SlotStatus;
use App\Enums\SpaceType;
use App\Models\Slot;
use App\Models\SlotRequest;
use App\Rules\MaxLines;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** Demande de créneau envoyée par l'exposant. */
class SubmitSlotRequestForm extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', SlotRequest::class);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(SpaceType::class)],
            'title' => ['required', 'string', 'max:150'],
            'summary' => ['required', 'string', 'max:800', new MaxLines(5)],
            'speakers' => ['required', 'string', 'max:500'],
            // Un seul créneau par demande.
            'first_choice_slot_id' => ['required', 'integer', 'exists:slots,id'],
        ];
    }

    /** Le créneau choisi doit être libre et appartenir au type d'espace demandé. */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $field = 'first_choice_slot_id';
                $type = SpaceType::tryFrom((string) $this->input('type'));

                if (! $type || ! $this->filled($field) || $validator->errors()->has($field)) {
                    return;
                }

                $slot = Slot::query()->with('space:id,type')->find($this->integer($field));

                if (! $slot || $slot->space->type !== $type) {
                    $validator->errors()->add($field, 'Ce créneau ne correspond pas à l\'espace choisi.');
                } elseif ($slot->status !== SlotStatus::Available) {
                    $validator->errors()->add($field, "Le créneau {$slot->code} n'est plus disponible.");
                } elseif ($this->alreadyRequested($slot)) {
                    $validator->errors()->add($field, "Vous avez déjà une demande en attente pour le créneau {$slot->code}.");
                }
            },
        ];
    }

    private function alreadyRequested(Slot $slot): bool
    {
        return SlotRequest::query()
            ->whereIn('exhibitor_id', $this->user()->exhibitorProfile()->select('id'))
            ->where('status', SlotRequestStatus::Pending)
            ->where('first_choice_slot_id', $slot->id)
            ->exists();
    }

    public function attributes(): array
    {
        return [
            'type' => 'espace',
            'title' => 'titre',
            'summary' => 'résumé',
            'speakers' => 'intervenants',
            'first_choice_slot_id' => 'créneau',
        ];
    }
}
