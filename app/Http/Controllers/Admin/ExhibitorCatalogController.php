<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exhibitor;
use App\Models\ExhibitorCatalogProfile;
use App\Models\User;
use App\Services\ExhibitorCatalogExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use ZipArchive;

class ExhibitorCatalogController extends Controller
{
    private function filtered(Request $request)
    {
        $query = Exhibitor::query()->with(['catalogProfile', 'commercial']);
        if ($term = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($term): void {
                $q->where('company_name', 'like', '%'.$term.'%')
                    ->orWhereHas('catalogProfile', fn ($p) => $p->where('email', 'like', '%'.$term.'%')->orWhere('company_description', 'like', '%'.$term.'%'));
            });
        }
        if ($request->filled('commercial_id')) $query->where('commercial_id', $request->integer('commercial_id'));
        $status = $request->query('status');
        if ($status === 'completed' || $status === 'draft') $query->whereHas('catalogProfile', fn ($q) => $q->where('status', $status));
        elseif ($status === 'empty') $query->whereDoesntHave('catalogProfile');
        return $query->orderBy(ExhibitorCatalogProfile::query()->select('updated_at')->whereColumn('exhibitor_id', 'exhibitors.id')->limit(1), 'desc')
            ->orderBy('company_name');
    }

    public function index(Request $request): View
    {
        $stats = [
            'total' => Exhibitor::count(),
            'completed' => Exhibitor::whereHas('catalogProfile', fn ($q) => $q->where('status', 'completed'))->count(),
            'draft' => Exhibitor::whereHas('catalogProfile', fn ($q) => $q->where('status', 'draft'))->count(),
            'empty' => Exhibitor::whereDoesntHave('catalogProfile')->count(),
        ];
        $exhibitors = $this->filtered($request)->paginate(15)->withQueryString();
        $commercials = User::query()->where('role', 'commercial')->orderBy('name')->get(['id', 'name']);
        return view('admin.catalog.index', compact('exhibitors', 'stats', 'commercials'));
    }

    public function show(int $exhibitor): View
    {
        return view('admin.catalog.show', ['exhibitor' => Exhibitor::with(['catalogProfile', 'commercial'])->findOrFail($exhibitor)]);
    }

    public function logo(int $exhibitor)
    {
        $record = Exhibitor::with('catalogProfile')->findOrFail($exhibitor);
        abort_unless($record->catalogProfile?->logo_path && Storage::disk('local')->exists($record->catalogProfile->logo_path), 404);
        return Storage::disk('local')->response($record->catalogProfile->logo_path);
    }

    public function export(Request $request, ExhibitorCatalogExportService $exporter)
    {
        $status = $request->query('status');
        if (!$request->filled('status')) $request->query->set('status', 'completed');
        $exhibitors = $this->filtered($request)->get();
        $path = $exporter->xlsx($exhibitors);
        return response()->download($path, 'REFRIGAIR_EXPO_2027_FICHES_CATALOGUE.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function logosZip(Request $request, ExhibitorCatalogExportService $exporter)
    {
        if (!$request->filled('status')) $request->query->set('status', 'completed');
        $exhibitors = $this->filtered($request)->get()->filter(fn ($e) => $e->catalogProfile?->logo_path && Storage::disk('local')->exists($e->catalogProfile->logo_path));
        $path = tempnam(sys_get_temp_dir(), 'catalog-logos-');
        $zip = new ZipArchive();
        abort_if($path === false || $zip->open($path, ZipArchive::OVERWRITE) !== true, 500);
        foreach ($exhibitors as $exhibitor) {
            $zip->addFromString($exporter->logoFilename($exhibitor), Storage::disk('local')->get($exhibitor->catalogProfile->logo_path));
        }
        $zip->close();
        return response()->download($path, 'REFRIGAIR_EXPO_2027_LOGOS.zip')->deleteFileAfterSend(true);
    }
}
