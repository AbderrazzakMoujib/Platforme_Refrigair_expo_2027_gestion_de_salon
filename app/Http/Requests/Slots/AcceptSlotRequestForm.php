<?php

namespace App\Http\Requests\Slots;

use Illuminate\Foundation\Http\FormRequest;

class AcceptSlotRequestForm extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('process', $this->route('slotRequest'));
    }

    public function rules(): array
    {
        return [
            'slot_id' => ['required', 'integer', 'exists:slots,id'],
        ];
    }

    public function attributes(): array
    {
        return ['slot_id' => 'créneau'];
    }
}
