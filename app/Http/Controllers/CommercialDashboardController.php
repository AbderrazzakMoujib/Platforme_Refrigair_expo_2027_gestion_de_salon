<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class CommercialDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $commercial = $request->user();

        return view('commercial.dashboard', [
            'commercial' => $commercial,
            'exhibitorsCount' => $commercial->exhibitors()->count(),
            'recentExhibitors' => $commercial->exhibitors()->latest()->limit(5)->get(),
            'commercialInitials' => collect(preg_split('/\s+/', trim($commercial->name)))
                ->filter()->take(2)
                ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
                ->implode('') ?: 'C',
        ]);
    }
}
