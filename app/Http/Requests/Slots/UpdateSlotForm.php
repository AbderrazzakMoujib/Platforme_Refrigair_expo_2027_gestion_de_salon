<?php

namespace App\Http\Requests\Slots;

use App\Enums\SlotStatus;
use App\Models\Slot;
use App\Rules\MaxLines;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Mise à jour d'un créneau depuis le planning commercial.
 *
 * Le commercial peut réserver pour :
 * - un exposant inscrit (holder_type = exhibitor) ;
 * - une autre personne / société non exposante (holder_type = external),
 *   avec ses coordonnées pour la joindre.
 */
class UpdateSlotForm extends FormRequest
{
    public const HOLDER_EXHIBITOR = 'exhibitor';

    public const HOLDER_EXTERNAL = 'external';

    /** Permet de rouvrir la bonne fenêtre du planning en cas d'erreur. */
    protected $errorBag = 'slot';

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('slot'));
    }

    /** Ne garde que les champs du type de titulaire choisi. */
    protected function prepareForValidation(): void
    {
        $type = $this->input('holder_type')
            ?? ($this->filled('exhibitor_id') ? self::HOLDER_EXHIBITOR : self::HOLDER_EXTERNAL);

        $this->merge(['holder_type' => $type]);

        if ($type === self::HOLDER_EXHIBITOR) {
            $this->merge(['exhibitor_name' => null, 'contact_name' => null, 'contact_email' => null, 'contact_phone' => null]);
        } elseif ($type === self::HOLDER_EXTERNAL) {
            $this->merge(['exhibitor_id' => null]);
        }

        // Créneau libre + titulaire renseigné mais statut laissé sur « Disponible » :
        // le commercial veut réserver → on passe en « Réservé ».
        // (Sur un créneau déjà pris, « Disponible » reste le moyen de le libérer.)
        $slot = $this->route('slot');
        $holderFilled = $type === self::HOLDER_EXHIBITOR ? $this->filled('exhibitor_id') : $this->filled('exhibitor_name');

        if (
            $slot instanceof Slot
            && $slot->status === SlotStatus::Available
            && $this->input('status') === SlotStatus::Available->value
            && $holderFilled
        ) {
            $this->merge(['status' => SlotStatus::Reserved->value]);
        }
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(SlotStatus::class)],
            'holder_type' => ['required', Rule::in([self::HOLDER_EXHIBITOR, self::HOLDER_EXTERNAL])],
            'exhibitor_id' => ['nullable', 'integer', 'exists:exhibitors,id'],
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

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $status = SlotStatus::tryFrom((string) $this->input('status'));

                if (! $status || ! $status->isTaken()) {
                    return;
                }

                if ($this->input('holder_type') === self::HOLDER_EXHIBITOR && ! $this->filled('exhibitor_id')) {
                    $validator->errors()->add('exhibitor_id', 'Choisissez l\'exposant pour qui vous réservez ce créneau.');
                }

                if ($this->input('holder_type') === self::HOLDER_EXTERNAL && ! $this->filled('exhibitor_name')) {
                    $validator->errors()->add('exhibitor_name', 'Indiquez le nom de la personne ou de la société pour qui vous réservez.');
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'statut',
            'holder_type' => 'type de titulaire',
            'exhibitor_id' => 'exposant',
            'exhibitor_name' => 'nom / société',
            'contact_name' => 'nom du contact',
            'contact_email' => 'e-mail du contact',
            'contact_phone' => 'téléphone du contact',
            'title' => 'titre',
            'summary' => 'résumé',
            'speakers' => 'intervenants',
        ];
    }
}
