<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class RefrigairFournituresSeeder extends Seeder
{
    /**
     * Catalog metadata readable from the existing image filenames and images.
     * No prices, quantities, or unsupported product specifications are supplied.
     * The equipment table's existing database defaults are retained for those fields.
     *
     * @var array<int, array{name: string, category: string, image: string}>
     */
    private const EQUIPMENT = [
        ['name' => 'Machine à café', 'category' => 'Restauration', 'image' => 'coffee-machine.png'],
        ['name' => 'Vitrine de comptoir', 'category' => 'Mobilier', 'image' => 'counter-window.png'],
        ['name' => 'Chaise design', 'category' => 'Mobilier', 'image' => 'design-chair.png'],
        ['name' => 'Comptoir d’accueil', 'category' => 'Mobilier', 'image' => 'front-counter.png'],
        ['name' => 'Table haute ronde', 'category' => 'Mobilier', 'image' => 'high-table.png'],
        ['name' => 'Chaise ISO', 'category' => 'Mobilier', 'image' => 'iso-chair.png'],
        ['name' => 'Plante décorative', 'category' => 'Mobilier', 'image' => 'plant.png'],
        ['name' => 'Tabouret haut en plastique', 'category' => 'Mobilier', 'image' => 'plastic-stool.png'],
        ['name' => 'Fauteuil président', 'category' => 'Mobilier', 'image' => 'president-chair.png'],
        ['name' => 'Table rectangulaire', 'category' => 'Mobilier', 'image' => 'rectongular-table.png'],
        ['name' => 'Table ronde', 'category' => 'Mobilier', 'image' => 'round-table.png'],
        ['name' => 'Tabouret de bar', 'category' => 'Mobilier', 'image' => 'stool-cook.png'],
        ['name' => 'Meuble de rangement', 'category' => 'Mobilier', 'image' => 'storage-unit.png'],
        ['name' => 'Téléviseur avec support', 'category' => 'Audiovisuel', 'image' => 'tv-with-bracket.png'],
        ['name' => 'Salon VIP', 'category' => 'Mobilier', 'image' => 'vip-sofa.png'],
    ];

    public function run(): void
    {
        $disk = Storage::disk('public');
        $prefix = 'equipment/refrigair/';
        $missingImages = collect(self::EQUIPMENT)
            ->pluck('image')
            ->map(fn (string $image): string => $prefix.$image)
            ->filter(fn (string $path): bool => ! $disk->exists($path))
            ->values();

        if ($missingImages->isNotEmpty()) {
            throw new RuntimeException(
                "Images manquantes sur le disque public. Transférez-les dans storage/app/public/equipment/refrigair/:\n- "
                .$missingImages->implode("\n- ")
            );
        }

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($prefix, &$created, &$skipped): void {
            foreach (self::EQUIPMENT as $item) {
                $imagePath = $prefix.$item['image'];

                // Match by image path OR catalog name; existing rows are never updated.
                $alreadyExists = Equipment::query()
                    ->where(function ($query) use ($imagePath, $item): void {
                        $query->where('image_path', $imagePath)
                            ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($item['name'])]);
                    })
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;

                    continue;
                }

                Equipment::query()->create([
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'image_path' => $imagePath,
                    // Do not expose products with unknown prices or stock counts.
                    'is_active' => false,
                ]);

                $created++;
            }
        });

        $this->command?->info("Équipements ajoutés : {$created}. Déjà présents et inchangés : {$skipped}.");
        $this->command?->warn('Prix et quantité restent aux valeurs par défaut de la base (0). Les nouveaux articles restent inactifs jusqu’à la saisie des valeurs exactes et leur activation depuis l’Admin.');
    }
}
