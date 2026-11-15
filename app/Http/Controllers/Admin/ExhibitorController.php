<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exhibitor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExhibitorController extends Controller
{
    private function layoutData(Request $request): array
    {
        $admin = $request->user();

        $initials = collect(
            preg_split('/\s+/', trim($admin->name))
        )
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: 'A';

        return [
            'admin' => $admin,
            'adminInitials' => $initials,
        ];
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'commercial_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'in:complete,partial,pending,no_quota'],
        ]);

        $badgeCountSql = '(SELECT COUNT(*) FROM badges
            WHERE badges.exhibitor_id = exhibitors.id)';

        $query = Exhibitor::query()
            ->with(['commercial:id,name', 'user:id,is_active'])
            ->withCount('badges');

        if (!empty($filters['search'])) {
            $term = trim($filters['search']);

            $query->where(function ($q) use ($term) {
                $q->where('company_name', 'like', "%{$term}%")
                    ->orWhere('contact_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('stand_number', 'like', "%{$term}%");
            });
        }

        if (!empty($filters['commercial_id'])) {
            $query->where('commercial_id', $filters['commercial_id']);
        }

        switch ($filters['status'] ?? '') {
            case 'complete':
                $query->where('authorized_badges', '>', 0)
                    ->whereRaw("$badgeCountSql >= exhibitors.authorized_badges");
                break;

            case 'partial':
                $query->where('authorized_badges', '>', 0)
                    ->whereRaw("$badgeCountSql > 0")
                    ->whereRaw("$badgeCountSql < exhibitors.authorized_badges");
                break;

            case 'pending':
                $query->where('authorized_badges', '>', 0)
                    ->whereDoesntHave('badges');
                break;

            case 'no_quota':
                $query->where('authorized_badges', 0);
                break;
        }

        $exhibitors = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $commercials = User::query()
            ->where('role', 'commercial')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.exhibitors.index', [
            ...$this->layoutData($request),
            'exhibitors' => $exhibitors,
            'commercials' => $commercials,
            'totalExhibitors' => Exhibitor::count(),
            'totalAuthorized' => (int) Exhibitor::sum('authorized_badges'),
            'totalRegistered' => (int) \App\Models\Badge::count(),
        ]);
    }

    public function show(
        Request $request,
        Exhibitor $exhibitor
    ): View {
        $exhibitor->load([
            'commercial:id,name,email,phone',
            'user:id,name,email,is_active',
        ])->loadCount('badges');

        $badges = $exhibitor->badges()
            ->latest()
            ->paginate(10);

        return view('admin.exhibitors.show', [
            ...$this->layoutData($request),
            'exhibitor' => $exhibitor,
            'badges' => $badges,
        ]);
    }
}
