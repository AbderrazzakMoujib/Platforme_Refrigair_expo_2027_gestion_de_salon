<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExhibitorDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->role === UserRole::Exposant, 403);

        $exhibitor = $user->exhibitorProfile()->first();
        $badgeCount = $exhibitor?->badges()->count() ?? 0;
        return view('exhibitor.dashboard', [
            'user' => $user,
            'exhibitor' => $exhibitor,
            'badgeCount' => $badgeCount,
        ]);
    }
}
