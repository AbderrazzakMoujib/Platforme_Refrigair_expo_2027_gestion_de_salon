<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CommercialEquipmentRequestController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Liste des demandes d'équipements
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $commercial = $request->user();

        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'nullable',
                'in:pending,approved,rejected',
            ],

            'exhibitor_id' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ]);

        // Récupérer uniquement les demandes
        // des exposants associés au commercial.

        $baseQuery = EquipmentRequest::query()
            ->whereHas('exhibitor', function ($query) use ($commercial) {
                $query->where('commercial_id', $commercial->id);
            });


        /*
        |--------------------------------------------------------------------------
        | Statistiques
        |--------------------------------------------------------------------------
        */

        $totalRequests = (clone $baseQuery)->count();

        $pendingCount = (clone $baseQuery)
            ->where('status', 'pending')
            ->count();

        $approvedCount = (clone $baseQuery)
            ->where('status', 'approved')
            ->count();

        $rejectedCount = (clone $baseQuery)
            ->where('status', 'rejected')
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Liste des demandes
        |--------------------------------------------------------------------------
        */

        $query = (clone $baseQuery)
            ->with([
                'exhibitor',
                'items.equipment',
            ])
            ->withCount('items')
            ->withSum(
                'items as requested_quantity',
                'quantity'
            );


        /*
        |--------------------------------------------------------------------------
        | Recherche
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['search'])) {

            $term = trim($filters['search']);

            $query->where(function ($query) use ($term) {

                $query->whereHas(
                    'exhibitor',
                    function ($exhibitor) use ($term) {

                        $exhibitor
                            ->where(
                                'company_name',
                                'like',
                                "%{$term}%"
                            )
                            ->orWhere(
                                'stand_number',
                                'like',
                                "%{$term}%"
                            )
                            ->orWhere(
                                'contact_name',
                                'like',
                                "%{$term}%"
                            )
                            ->orWhere(
                                'email',
                                'like',
                                "%{$term}%"
                            );

                    }
                )

                ->orWhereHas(
                    'items',
                    function ($item) use ($term) {

                        $item->where(
                            'equipment_name',
                            'like',
                            "%{$term}%"
                        );

                    }
                );

                // Recherche par numéro de demande.

                if (ctype_digit($term)) {

                    $query->orWhere('id', (int) $term);

                }

                // Recherche par référence DEM-0001.

                if (preg_match('/^#?DEM-(\d+)$/i', $term, $matches)) {

                    $query->orWhere('id', (int) $matches[1]);

                }

            });

        }


        /*
        |--------------------------------------------------------------------------
        | Filtre par statut
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['status'])) {

            $query->where(
                'status',
                $filters['status']
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Filtre par exposant
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['exhibitor_id'])) {

            $query->where(
                'exhibitor_id',
                $filters['exhibitor_id']
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $requests = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Exposants du commercial
        |--------------------------------------------------------------------------
        */

        $exhibitors = $commercial
            ->exhibitors()
            ->orderBy('company_name')
            ->get([
                'id',
                'company_name',
            ]);


        return view('commercial.equipment-requests.index', [

            ...$this->layoutData($request),

            'requests' => $requests,

            'exhibitors' => $exhibitors,

            'totalRequests' => $totalRequests,

            'pendingCount' => $pendingCount,

            'approvedCount' => $approvedCount,

            'rejectedCount' => $rejectedCount,

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Formulaire de création
    |--------------------------------------------------------------------------
    */

    public function create(Request $request): View
    {
        $commercial = $request->user();


        // Exposants associés au commercial connecté.

        $exhibitors = $commercial
            ->exhibitors()
            ->orderBy('company_name')
            ->get();


        // Catalogue des équipements actifs.

        $equipment = Equipment::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();


        // Catégories disponibles.

        $categories = $equipment
            ->pluck('category')
            ->filter()
            ->unique()
            ->values();


        return view('commercial.equipment-requests.create', [

            ...$this->layoutData($request),

            'exhibitors' => $exhibitors,

            'equipment' => $equipment,

            'categories' => $categories,

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Enregistrement d'une demande
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {

        /*
        |--------------------------------------------------------------------------
        | Validation du formulaire
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([

            'exhibitor_id' => [
                'required',
                'integer',
                'min:1',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],

            'items.*' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

        ], [

            'exhibitor_id.required' =>
                'Veuillez sélectionner un exposant.',

            'items.required' =>
                'Veuillez sélectionner au moins un équipement.',

            'items.min' =>
                'Veuillez sélectionner au moins un équipement.',

            'items.max' =>
                'Vous pouvez sélectionner au maximum 100 équipements différents.',

            'items.*.integer' =>
                'La quantité doit être un nombre entier.',

            'items.*.min' =>
                'La quantité doit être supérieure ou égale à 1.',

            'items.*.max' =>
                'La quantité maximale est de 100 par équipement.',

            'notes.max' =>
                'Les notes ne doivent pas dépasser 2000 caractères.',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Transaction sécurisée
        |--------------------------------------------------------------------------
        */

        $order = DB::transaction(function () use ($request, $data) {


            /*
            |--------------------------------------------------------------------------
            | Vérification de l'exposant
            |--------------------------------------------------------------------------
            */

            $exhibitor = $request->user()
                ->exhibitors()
                ->whereKey($data['exhibitor_id'])
                ->lockForUpdate()
                ->firstOrFail();


            /*
            |--------------------------------------------------------------------------
            | Préparation des équipements
            |--------------------------------------------------------------------------
            */

            $items = [];

            $totalCents = 0;

            $selected = collect($data['items'])
                ->sortKeys();


            foreach ($selected as $id => $quantity) {


                /*
                |--------------------------------------------------------------------------
                | Vérification de l'identifiant
                |--------------------------------------------------------------------------
                */

                if (
                    !ctype_digit((string) $id)
                    || (int) $id < 1
                    || (string) (int) $id !== (string) $id
                ) {

                    throw ValidationException::withMessages([

                        'items' => 'Équipement invalide.',

                    ]);

                }


                /*
                |--------------------------------------------------------------------------
                | Récupération de l'équipement
                |--------------------------------------------------------------------------
                */

                $equipment = Equipment::query()
                    ->whereKey((int) $id)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();


                if (!$equipment) {

                    throw ValidationException::withMessages([

                        'items' =>
                            'Un équipement sélectionné est indisponible.',

                    ]);

                }


                /*
                |--------------------------------------------------------------------------
                | Vérification du stock
                |--------------------------------------------------------------------------
                */

                $quantity = (int) $quantity;


                if ($quantity > $equipment->quantity_available) {

                    throw ValidationException::withMessages([

                        'items' =>

                            "Stock insuffisant pour {$equipment->name}. "

                            . "Disponible : {$equipment->quantity_available}.",

                    ]);

                }


                /*
                |--------------------------------------------------------------------------
                | Calcul sécurisé du prix
                |--------------------------------------------------------------------------
                */

                // Le prix provient exclusivement de la base
                // de données et jamais du navigateur.

                $priceCents = (int) round(

                    (float) $equipment->price * 100

                );


                $subtotalCents = $priceCents * $quantity;


                $totalCents += $subtotalCents;


                /*
                |--------------------------------------------------------------------------
                | Préparer la ligne de commande
                |--------------------------------------------------------------------------
                */

                $items[] = [

                    'equipment_id' => $equipment->id,

                    'equipment_name' => $equipment->name,

                    'quantity' => $quantity,

                    'unit_price' => number_format(

                        $priceCents / 100,

                        2,

                        '.',

                        ''

                    ),

                    'subtotal' => number_format(

                        $subtotalCents / 100,

                        2,

                        '.',

                        ''

                    ),

                ];

            }


            /*
            |--------------------------------------------------------------------------
            | Vérification finale
            |--------------------------------------------------------------------------
            */

            if (empty($items)) {

                throw ValidationException::withMessages([

                    'items' =>
                        'Veuillez sélectionner au moins un équipement.',

                ]);

            }


            /*
            |--------------------------------------------------------------------------
            | Création de la demande
            |--------------------------------------------------------------------------
            */

            $order = EquipmentRequest::create([

                'exhibitor_id' => $exhibitor->id,

                'status' => 'pending',

                'total_amount' => number_format(

                    $totalCents / 100,

                    2,

                    '.',

                    ''

                ),

                'notes' => $data['notes'] ?? null,

            ]);


            /*
            |--------------------------------------------------------------------------
            | Enregistrement des équipements
            |--------------------------------------------------------------------------
            */

            $order->items()->createMany($items);


            // Le stock n'est pas déduit ici.
            // Seul l'administrateur pourra valider
            // la demande et déduire les quantités.


            return $order;

        }, 3);


        /*
        |--------------------------------------------------------------------------
        | Redirection vers la fiche
        |--------------------------------------------------------------------------
        */

        return redirect()

            ->route(

                'commercial.equipment-requests.show',

                $order

            )

            ->with(

                'status',

                'Demande d’équipements envoyée avec succès.'

            );

    }


    /*
    |--------------------------------------------------------------------------
    | Détail d'une demande
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        EquipmentRequest $equipmentRequest
    ): View {

        $commercial = $request->user();


        /*
        |--------------------------------------------------------------------------
        | Protection contre les accès non autorisés
        |--------------------------------------------------------------------------
        */

        $equipmentRequest = EquipmentRequest::query()

            ->whereKey($equipmentRequest->id)

            ->whereHas(
                'exhibitor',
                function ($query) use ($commercial) {

                    $query->where(

                        'commercial_id',

                        $commercial->id

                    );

                }
            )

            ->with([

                'exhibitor',

                'items.equipment',

                'reviewer',

            ])

            ->firstOrFail();


        return view('commercial.equipment-requests.show', [

            ...$this->layoutData($request),

            'equipmentRequest' => $equipmentRequest,

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Informations du layout commercial
    |--------------------------------------------------------------------------
    */

    private function layoutData(Request $request): array
    {

        $commercial = $request->user();


        $commercialInitials = collect(

            preg_split('/\s+/', trim($commercial->name))

        )

            ->filter()

            ->take(2)

            ->map(fn (string $part) =>

                mb_strtoupper(

                    mb_substr($part, 0, 1)

                )

            )

            ->implode('') ?: 'C';


        return [

            'commercial' => $commercial,

            'commercialInitials' => $commercialInitials,

        ];

    }

}
