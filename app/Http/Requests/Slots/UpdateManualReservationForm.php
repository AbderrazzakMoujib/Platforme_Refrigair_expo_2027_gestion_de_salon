<?php

namespace App\Http\Requests\Slots;

use App\Models\Slot;
use App\Rules\MaxLines;
use Illuminate\Foundation\Http\FormRequest;

class UpdateManualReservationForm extends FormRequest
{
    protected $errorBag = 'slot';

    public function authorize(): bool
    {
        $slot = $this->route('slot');

        return $slot instanceof Slot && ($this->user()?->can('manageManualReservation', $slot) ?? false);
    }

    public function rules(): array
    {
        return [
            'exhibitor_name' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'title' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:1000', new MaxLines(5)],
            'speakers' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
