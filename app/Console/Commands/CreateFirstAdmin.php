<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateFirstAdmin extends Command
{
    protected $signature = 'app:create-admin';

    protected $description = 'Crée le premier compte administrateur de manière interactive';

    public function handle(): int
    {
        if (User::query()->where('role', UserRole::Admin->value)->exists()) {
            $this->components->error('Un compte Admin existe déjà.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Nom complet'));
        $email = strtolower(trim((string) $this->ask('Email')));
        $password = (string) $this->secret('Mot de passe (minimum 8 caractères)');
        $confirmation = (string) $this->secret('Confirmer le mot de passe');

        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->components->error('Nom ou email invalide.');

            return self::FAILURE;
        }

        if (strlen($password) < 8 || $password !== $confirmation) {
            $this->components->error('Les mots de passe ne correspondent pas ou sont trop courts.');

            return self::FAILURE;
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->components->error('Cet email est déjà utilisé.');

            return self::FAILURE;
        }

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $this->components->info('Premier Admin créé avec succès.');

        return self::SUCCESS;
    }
}
