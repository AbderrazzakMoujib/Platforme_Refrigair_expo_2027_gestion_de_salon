<?php

namespace App\Http\Requests\Slots;

use App\Enums\SlotRequestStatus;
use App\Enums\SlotStatus;
use App\Enums\SpaceType;
use App\Models\Exhibitor;
use App\Models\Slot;
use App\Models\SlotRequest;
use App\Rules\MaxLines;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SubmitCommercialSlotRequestForm extends FormRequest
{
    public function authorize(): bool
    {
        $exhibitor = Exhibitor::query()->find($this->input('exhibitor_id'));

        return $exhibitor !== null && $this->user()->can('createFor', [\App\Models\SlotRequest::class, $exhibitor]);
    }

    public function rules(): array
    {
        return [
            'exhibitor_id' => ['required', 'integer', Rule::exists('exhibitors', 'id')->where('commercial_id', $this->user()->id)],
            'type' => ['required', Rule::enum(SpaceType::class)],
            'title' => ['required', 'string', 'max:150'],
            'summary' => ['required', 'string', 'max:800', new MaxLines(5)],
            'speakers' => ['required', 'string', 'max:500'],
            'first_choice_slot_id' => ['required', 'integer', 'exists:slots,id'],
            'second_choice_slot_id' => ['required', 'integer', 'different:first_choice_slot_id', 'exists:slots,id'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $type = SpaceType::tryFrom((string) $this->input('type'));
            $exhibitorId = (int) $this->input('exhibitor_id');

            if (! $type || $exhibitorId < 1) {
                return;
            }

            foreach (['first_choice_slot_id', 'second_choice_slot_id'] as $field) {
                if (! $this->filled($field) || $validator->errors()->has($field)) {
                    continue;
                }

                $slot = Slot::query()->with('space:id,type')->find($this->integer($field));
                if (! $slot || $slot->space?->type !== $type) {
                    $validator->errors()->add($field, 'Le créneau choisi ne correspond pas à cet espace.');
                } elseif ($slot->status !== SlotStatus::Available) {
                    $validator->errors()->add($field, "Le créneau {$slot->code} n'est plus disponible.");
                } elseif (SlotRequest::query()->where('exhibitor_id', $exhibitorId)
                    ->where('status', SlotRequestStatus::Pending)
                    ->where(fn ($query) => $query->where('first_choice_slot_id', $slot->id)->orWhere('second_choice_slot_id', $slot->id))
                    ->exists()) {
                    $validator->errors()->add($field, "Une demande en attente existe déjà pour le créneau {$slot->code}.");
                }
            }
        }];
    }
}
