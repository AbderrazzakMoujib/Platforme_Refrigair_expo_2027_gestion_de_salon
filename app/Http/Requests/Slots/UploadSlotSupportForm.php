<?php

namespace App\Http\Requests\Slots;

use Illuminate\Foundation\Http\FormRequest;

/** Support de présentation : PPT, PPTX ou PDF, 20 Mo maximum. */
class UploadSlotSupportForm extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('uploadSupport', $this->route('slot'));
    }

    public function rules(): array
    {
        return [
            'support' => [
                'required',
                'file',
                'mimes:ppt,pptx,pdf',
                'extensions:ppt,pptx,pdf',
                'max:20480',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'support.mimes' => 'Le support doit être un fichier PPT, PPTX ou PDF.',
            'support.extensions' => 'Le support doit être un fichier PPT, PPTX ou PDF.',
            'support.max' => 'Le support ne doit pas dépasser 20 Mo.',
        ];
    }

    public function attributes(): array
    {
        return ['support' => 'support'];
    }
}
