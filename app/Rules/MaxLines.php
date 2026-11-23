<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Limite le nombre de lignes d'un texte (ex. résumé de 5 lignes maximum). */
class MaxLines implements ValidationRule
{
    public function __construct(private int $max) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $lines = preg_split('/\R/', trim((string) $value)) ?: [];

        if (count($lines) > $this->max) {
            $fail("Le :attribute ne doit pas dépasser {$this->max} lignes.");
        }
    }
}
