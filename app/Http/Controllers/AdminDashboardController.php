<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $admin = $request->user();

        return view('admin.dashboard', [
            'admin' => $admin,
            'adminInitials' => $this->initials($admin->name),
            'commercialsCount' => User::query()->where('role', 'commercial')->where('is_active', true)->count(),
            'exposantsCount' => User::query()->where('role', 'exposant')->count(),
            'recentAccounts' => User::query()
                ->select(['id', 'name', 'role', 'created_at'])
                ->latest('created_at')
                ->limit(5)
                ->get(),
        ]);
    }

    private function initials(string $name): string
    {
        return collect(preg_split('/\s+/', trim($name)))
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: 'A';
    }
}
