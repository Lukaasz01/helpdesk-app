<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardController extends Controller {
    public function __invoke(Request $request) {
        $user = auth()->user();

        // Query base respeitando o RBAC (cliente vê só os dele, técnico só os dele, admin vê todos)
        $query = Ticket::query()
            ->when($user->hasRole('client'), fn($q) => $q->where('client_id', $user->id))
            ->when($user->hasRole('technician'), fn($q) => $q->where('technician_id', $user->id));

        $stats = [
            'total'       => (clone $query)->count(),
            'open'        => (clone $query)->where('status', 'open')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'resolved'    => (clone $query)->whereIn('status', ['resolved', 'closed'])->count(),
            'urgent'      => (clone $query)->where('priority', 'urgent')->whereNotIn('status', ['resolved', 'closed'])->count(),
        ];

        // Últimos 5 chamados recentes
        $recentTickets = (clone $query)->with(['client', 'technician'])->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'recentTickets'));
    }
}