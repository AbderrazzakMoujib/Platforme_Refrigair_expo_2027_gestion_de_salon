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
            'summary' => [
                'required',
                'string',
                'max:800',
                new MaxLines(5),
            ],
            'speakers' => ['required', 'string', 'max:500'],

            'first_choice_slot_id' => [
                'required',
                'integer',
                'exists:slots,id',
            ],
            
'second_choice_slot_id' => [
    'nullable',
    'integer',
    'different:first_choice_slot_id',
    'exists:slots,id',
],

        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $type = SpaceType::tryFrom(
                    (string) $this->input('type')
                );

                if (! $type) {
                    return;
                }

                $fields = [
                    'first_choice_slot_id',
                    'second_choice_slot_id',
                ];

                foreach ($fields as $field) {
                    if (
                        ! $this->filled($field)
                        || $validator->errors()->has($field)
                    ) {
                        continue;
                    }

                    $slot = Slot::query()
                        ->with('space:id,type')
                        ->find($this->integer($field));

                    if (! $slot || $slot->space?->type !== $type) {
                        $validator->errors()->add(
                            $field,
                            'Le créneau choisi ne correspond pas à cet espace.'
                        );
                    } elseif ($slot->status !== SlotStatus::Available) {
                        $validator->errors()->add(
                            $field,
                            "Le créneau {$slot->code} n'est plus disponible."
                        );
                    } elseif ($this->alreadyRequested($slot)) {
                        $validator->errors()->add(
                            $field,
                            "Vous avez déjà une demande en attente pour le créneau {$slot->code}."
                        );
                    }
                }
            },
        ];
    }

    private function alreadyRequested(Slot $slot): bool
    {
        $exhibitorId = $this->user()
            ->exhibitorProfile()
            ->firstOrFail()
            ->id;

        return SlotRequest::query()
            ->where('exhibitor_id', $exhibitorId)
            ->where('status', SlotRequestStatus::Pending)
            ->where(function ($query) use ($slot): void {
                $query
                    ->where('first_choice_slot_id', $slot->id)
                    ->orWhere('second_choice_slot_id', $slot->id);
            })
            ->exists();
    }

    public function attributes(): array
    {
        return [
            'type' => 'espace',
            'title' => 'titre',
            'summary' => 'résumé',
            'speakers' => 'intervenants',
            'first_choice_slot_id' => 'premier créneau',
            'second_choice_slot_id' => 'deuxième créneau',
        ];
    }
}
