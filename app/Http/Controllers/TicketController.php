<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $tickets = Ticket::with(['client', 'technician'])
            ->when($user->hasRole('client'), fn ($q) => $q->where('client_id', $user->id))
            ->when($user->hasRole('technician'), fn ($q) => $q->where('technician_id', $user->id))

            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('code', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })

            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))

            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))

            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('tickets.create');
    }

    public function store(StoreTicketRequest $request)
    {
        $ticket = Ticket::create([
            'code' => Ticket::gerarCodigo(),
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'client_id' => auth()->id(),
            'status' => 'open',
        ]);

        return redirect()->route('tickets.index')
            ->with('status', "Chamado #{$ticket->code} aberto com sucesso!");
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load(['client', 'technician', 'comments.user']);

        $technicians = User::role('technician')->get();

        return view('tickets.show', compact('ticket', 'technicians'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
            // Só aceita como responsável quem de fato tem o papel de técnico:
            // "exists:users,id" sozinho deixaria atribuir o chamado a um cliente.
            'technician_id' => ['nullable', Rule::in(User::role('technician')->pluck('id'))],
        ]);

        $data = [
            'status' => $request->status,
            'technician_id' => $request->technician_id,
        ];

        if ($request->status === 'resolved' && ! $ticket->resolved_at) {
            $data['resolved_at'] = now();
        }

        $ticket->update($data);

        return back()->with('status', 'Chamado atualizado com sucesso!');
    }
}
