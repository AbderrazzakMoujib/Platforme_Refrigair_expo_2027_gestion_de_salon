<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SalonController extends Controller
{
    private const STATUSES = [
        'planned',
        'active',
        'completed',
        'cancelled',
    ];

    private function layoutData(Request $request): array
    {
        $admin = $request->user();

        return [
            'admin' => $admin,
            'adminInitials' => collect(
                preg_split('/\s+/', trim($admin->name))
            )
                ->filter()
                ->take(2)
                ->map(fn (string $part) =>
                    mb_strtoupper(mb_substr($part, 0, 1))
                )
                ->implode('') ?: 'A',
        ];
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => [
                'nullable',
                Rule::in(self::STATUSES),
            ],
        ]);

        $salons = Salon::query()
            ->when(
                !empty($filters['search']),
                function ($query) use ($filters) {
                    $term = trim($filters['search']);

                    $query->where(function ($q) use ($term) {
                        $q->where('name', 'like', "%{$term}%")
                            ->orWhere('edition', 'like', "%{$term}%")
                            ->orWhere('city', 'like', "%{$term}%");
                    });
                }
            )
            ->when(
                !empty($filters['status']),
                fn ($query) =>
                    $query->where('status', $filters['status'])
            )
            ->orderByDesc('start_date')
            ->paginate(10)
            ->withQueryString();

        return view('admin.salons.index', [
            ...$this->layoutData($request),
            'salons' => $salons,
            'totalSalons' => Salon::count(),
            'activeSalons' => Salon::where('status', 'active')->count(),
            'plannedSalons' => Salon::where('status', 'planned')->count(),
            'registrationOpenCount' => Salon::where(
                'registration_open',
                true
            )->count(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.salons.form', [
            ...$this->layoutData($request),
            'salon' => new Salon([
                'status' => 'planned',
                'registration_open' => false,
            ]),
            'pageTitle' => 'Créer un salon',
            'formAction' => route('admin.salons.store'),
            'formMethod' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $salon = Salon::create($data);

        return redirect()
            ->route('admin.salons.show', $salon)
            ->with('status', 'Salon créé avec succès.');
    }

    public function show(Request $request, Salon $salon): View
    {
        return view('admin.salons.show', [
            ...$this->layoutData($request),
            'salon' => $salon,
        ]);
    }

    public function edit(Request $request, Salon $salon): View
    {
        return view('admin.salons.form', [
            ...$this->layoutData($request),
            'salon' => $salon,
            'pageTitle' => 'Modifier le salon',
            'formAction' => route('admin.salons.update', $salon),
            'formMethod' => 'PUT',
        ]);
    }

    public function update(
        Request $request,
        Salon $salon
    ): RedirectResponse {
        $salon->update($this->validated($request));

        return redirect()
            ->route('admin.salons.show', $salon)
            ->with('status', 'Salon modifié avec succès.');
    }

    public function toggleRegistration(
        Salon $salon
    ): RedirectResponse {
        if (! in_array($salon->status, ['planned', 'active'], true)) {
            return back()->withErrors([
                'registration' =>
                    'Les inscriptions ne peuvent pas être ouvertes pour un salon terminé ou annulé.',
            ]);
        }

        $salon->update([
            'registration_open' => ! $salon->registration_open,
        ]);

        return back()->with(
            'status',
            $salon->registration_open
                ? 'Inscriptions ouvertes.'
                : 'Inscriptions fermées.'
        );
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'edition' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:150'],
            'venue' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(self::STATUSES)],
            'registration_open' => ['required', 'boolean'],
        ]);

        if (
            $data['registration_open']
            && in_array(
                $data['status'],
                ['completed', 'cancelled'],
                true
            )
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'registration_open' =>
                    'Les inscriptions doivent être fermées pour un salon terminé ou annulé.',
            ]);
        }

        return $data;
    }
}
