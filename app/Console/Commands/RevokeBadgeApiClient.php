<?php

namespace App\Console\Commands;

use App\Models\BadgeApiClient;
use Illuminate\Console\Command;

class RevokeBadgeApiClient extends Command
{
    protected $signature = 'badge-api:client:revoke {client : Identifiant numérique du client API}';

    protected $description = 'Révoque un jeton client de l’API des badges';

    public function handle(): int
    {
        $client = BadgeApiClient::query()->find($this->argument('client'));

        if (! $client) {
            $this->error('Client API introuvable.');

            return self::FAILURE;
        }

        $client->forceFill(['is_active' => false])->save();
        $this->info("Jeton révoqué pour {$client->name} (#{$client->id}).");

        return self::SUCCESS;
    }
}
