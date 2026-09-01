<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $this->authorize('comment', $ticket);

        $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $ticket->comments()->create([
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);

        return back()->with('status', 'Comentário adicionado com sucesso!');
    }
}
