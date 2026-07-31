<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Http\Requests\StoreTicketRequest;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // RBAC: Filtra chamados baseados no perfil do usuário
        $tickets = Ticket::with(['client', 'technician'])
            ->when($user->hasRole('client'), fn($q) => $q->where('client_id', $user->id))
            ->when($user->hasRole('technician'), fn($q) => $q->where('technician_id', $user->id))
            ->latest()
            ->paginate(10);

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('tickets.create');
    }

    public function store(StoreTicketRequest $request)
    {
        // Gera um código único amigável: Ex. OS-2026-A8F2
        $code = 'OS-' . date('Y') . '-' . strtoupper(substr(uniqid(), -4));

        $ticket = Ticket::create([
            'code' => $code,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'client_id' => auth()->id(),
            'status' => 'open',
        ]);

        return redirect()->route('tickets.index')
            ->with('status', "Chamado #{$ticket->code} aberto com sucesso!");
    }

    public function show(Ticket $ticket) {
        $ticket->load(['client', 'technician']);
        
        $technicians = \App\Models\User::role('technician')->get();

        return view('tickets.show', compact('ticket', 'technicians'));
    }
}