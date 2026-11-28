<?php

namespace App\Http\Controllers;

use App\Models\Exhibitor;
use App\Models\ExhibitorCatalogProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CommercialCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $commercial = $request->user();
        $query = $commercial->exhibitors()->with('catalogProfile')->withCount('catalogProfile');
        if ($term = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($term): void {
                $q->where('company_name', 'like', '%'.$term.'%')->orWhereHas('catalogProfile', fn ($p) => $p->where('email', 'like', '%'.$term.'%'));
            });
        }
        if ($request->query('status') === 'completed') $query->whereHas('catalogProfile', fn ($q) => $q->where('status', 'completed'));
        elseif ($request->query('status') === 'draft') $query->whereHas('catalogProfile', fn ($q) => $q->where('status', 'draft'));
        elseif ($request->query('status') === 'empty') $query->whereDoesntHave('catalogProfile');
        $exhibitors = $query->orderBy(ExhibitorCatalogProfile::query()->select('updated_at')->whereColumn('exhibitor_id', 'exhibitors.id')->limit(1), 'desc')
            ->orderBy('company_name')->paginate(15)->withQueryString();
        $stats = [
            'total' => $commercial->exhibitors()->count(),
            'completed' => $commercial->exhibitors()->whereHas('catalogProfile', fn ($q) => $q->where('status', 'completed'))->count(),
            'draft' => $commercial->exhibitors()->whereHas('catalogProfile', fn ($q) => $q->where('status', 'draft'))->count(),
            'empty' => $commercial->exhibitors()->whereDoesntHave('catalogProfile')->count(),
        ];
        return view('commercial.catalog.index', compact('commercial', 'exhibitors', 'stats'));
    }

    public function show(Request $request, int $exhibitor): View
    {
        $commercial = $request->user();
        $record = $commercial->exhibitors()->with('catalogProfile')->findOrFail($exhibitor);
        return view('commercial.catalog.show', ['commercial' => $commercial, 'exhibitor' => $record]);
    }

    public function logo(Request $request, int $exhibitor)
    {
        $record = $request->user()->exhibitors()->with('catalogProfile')->findOrFail($exhibitor);
        abort_unless($record->catalogProfile?->logo_path && Storage::disk('local')->exists($record->catalogProfile->logo_path), 404);
        return Storage::disk('local')->response($record->catalogProfile->logo_path);
    }
}
