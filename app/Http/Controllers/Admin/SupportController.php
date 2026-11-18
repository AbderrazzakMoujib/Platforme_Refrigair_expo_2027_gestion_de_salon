<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(Request $request): View
    {
        $admin = $request->user();

        $tickets = SupportTicket::query()
            ->with([
                'exhibitor:id,company_name',
                'commercial:id,name',
            ])
            ->latest()
            ->paginate(15);

        return view('admin.support.index', [
            'admin' => $admin,
            'adminInitials' => $admin->initials,
            'tickets' => $tickets,
        ]);
    }

    public function show(
        Request $request,
        SupportTicket $ticket
    ): View {
        $admin = $request->user();

        $ticket->load([
            'exhibitor',
            'commercial',
            'messages.user',
        ]);

        return view('admin.support.show', [
            'admin' => $admin,
            'adminInitials' => $admin->initials,
            'ticket' => $ticket,
        ]);
    }
}
