<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Salon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExhibitorBadgeController extends Controller
{
    private function profile(Request $request)
    {
        return $request->user()
            ->exhibitorProfile()
            ->firstOrFail();
    }

    private function owns(Request $request, Badge $badge): void
    {
        abort_unless(
            $request->user()
                ->exhibitorProfile()
                ->whereKey($badge->exhibitor_id)
                ->exists(),
            403
        );
    }

    public function index(Request $request): View
    {
        $exhibitor = $this->profile($request);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => [
                'nullable',
                Rule::in(['all', 'active', 'inactive']),
            ],
        ]);

        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? 'all';

        // Statistiques réelles, indépendantes des filtres.
        $count = $exhibitor->badges()->count();

        $authorized = (int) $exhibitor->authorized_badges;
        $remaining = max(0, $authorized - $count);

        $progress = $authorized > 0
            ? min(100, (int) round($count / $authorized * 100))
            : 0;

        $activeCount = $exhibitor->badges()
            ->where('status', 'active')
            ->count();

        $inactiveCount = $exhibitor->badges()
            ->where('status', 'inactive')
            ->count();

        // Recherche et filtre uniquement sur ses badges.
        $query = $exhibitor->badges();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('function', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $badges = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Informations générales du prochain salon.
        $salon = Salon::query()
            ->whereDate('end_date', '>=', today())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderBy('start_date')
            ->first();

        return view('exhibitor.badges.index', [
            'user' => $request->user(),
            'exhibitor' => $exhibitor,
            'badges' => $badges,

            'count' => $count,
            'authorized' => $authorized,
            'remaining' => $remaining,
            'progress' => $progress,
            'activeCount' => $activeCount,
            'inactiveCount' => $inactiveCount,

            'search' => $search,
            'status' => $status,
            'salon' => $salon,
        ]);
    }

    public function create(Request $request): View
    {
        $exhibitor = $this->profile($request);

        abort_if(
            $exhibitor->badges()->count()
                >= $exhibitor->authorized_badges,
            422
        );

        return view('exhibitor.badges.form', [
            'user' => $request->user(),
            'badge' => new Badge(),
            'action' => route('exhibitor.badges.store'),
            'method' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $exhibitor = $this->profile($request);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'function' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        DB::transaction(function () use ($exhibitor, $data) {
            $locked = $exhibitor->newQuery()
                ->lockForUpdate()
                ->findOrFail($exhibitor->id);

            if (
                $locked->badges()->count()
                >= $locked->authorized_badges
            ) {
                abort(422, 'Quota de badges atteint.');
            }

            $locked->badges()->create($data);
        });

        return redirect()
            ->route('exhibitor.badges.index')
            ->with('status', 'Badge ajouté.');
    }

    public function edit(Request $request, Badge $badge): View
    {
        $this->owns($request, $badge);

        return view('exhibitor.badges.form', [
            'user' => $request->user(),
            'badge' => $badge,
            'action' => route('exhibitor.badges.update', $badge),
            'method' => 'put',
        ]);
    }

    public function update(
        Request $request,
        Badge $badge
    ): RedirectResponse {
        $this->owns($request, $badge);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'function' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        $badge->update($data);

        return back()->with(
            'status',
            'Badge modifié.'
        );
    }

    public function destroy(
        Request $request,
        Badge $badge
    ): RedirectResponse {
        $this->owns($request, $badge);

        $badge->delete();

        return back()->with(
            'status',
            'Badge supprimé.'
        );
    }

    // Génération QR en SVG.
    // Accessible uniquement au propriétaire du badge.
    public function qr(
        Request $request,
        Badge $badge
    ): Response {
        $this->owns($request, $badge);

        // Ce lien mène au contrôle Admin.
        $verificationUrl = route(
            'admin.badges.verify',
            $badge->qr_token
        );

        $qrCode = new QrCode(
            data: $verificationUrl,
            size: 280,
            margin: 10
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);

        return response(
            $result->getString(),
            200,
            [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
}
