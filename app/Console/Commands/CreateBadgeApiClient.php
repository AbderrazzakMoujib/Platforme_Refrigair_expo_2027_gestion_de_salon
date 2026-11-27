<?php

namespace App\Console\Commands;

use App\Models\BadgeApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateBadgeApiClient extends Command
{
    protected $signature = 'badge-api:client:create {name : Nom du site ou de l’application autorisée}';

    protected $description = 'Crée un jeton client révocable pour l’API des badges';

    public function handle(): int
    {
        $name = trim((string) $this->argument('name'));

        if ($name === '' || mb_strlen($name) > 120) {
            $this->error('Le nom doit contenir entre 1 et 120 caractères.');

            return self::FAILURE;
        }

        $plainTextToken = Str::random(64);
        $client = BadgeApiClient::query()->create([
            'name' => $name,
            'token_hash' => hash('sha256', $plainTextToken),
            'is_active' => true,
            'expires_at' => now()->addYear(),
        ]);

        $this->info("Client créé : #{$client->id} — {$client->name}");
        $this->warn('Copiez ce jeton maintenant : il ne sera plus affiché.');
        $this->line($plainTextToken);

        return self::SUCCESS;
    }
}
