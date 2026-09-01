<?php

use App\Models\Ticket;

it('lista para o cliente apenas os chamados que ele abriu', function () {
    $dono = cliente();
    Ticket::factory()->ownedBy($dono)->create(['title' => 'Impressora sem tinta']);
    Ticket::factory()->create(['title' => 'Chamado de outra pessoa']);

    $this->actingAs($dono)
        ->get(route('tickets.index'))
        ->assertOk()
        ->assertSee('Impressora sem tinta')
        ->assertDontSee('Chamado de outra pessoa');
});

it('lista para o técnico apenas os chamados atribuídos a ele', function () {
    $tecnico = tecnico();
    Ticket::factory()->inProgress($tecnico)->create(['title' => 'Meu atendimento']);
    Ticket::factory()->create(['title' => 'Atendimento alheio']);

    $this->actingAs($tecnico)
        ->get(route('tickets.index'))
        ->assertOk()
        ->assertSee('Meu atendimento')
        ->assertDontSee('Atendimento alheio');
});

it('mostra ao administrador os chamados de todos', function () {
    $admin = administrador();
    Ticket::factory()->create(['title' => 'Chamado do cliente A']);
    Ticket::factory()->create(['title' => 'Chamado do cliente B']);

    $this->actingAs($admin)
        ->get(route('tickets.index'))
        ->assertOk()
        ->assertSee('Chamado do cliente A')
        ->assertSee('Chamado do cliente B');
});

it('abre um chamado e o vincula a quem o criou', function () {
    $autor = cliente();

    $this->actingAs($autor)
        ->post(route('tickets.store'), [
            'title' => 'Notebook não liga',
            'description' => 'Aperto o botão e nada acontece.',
            'priority' => 'high',
        ])
        ->assertRedirect(route('tickets.index'));

    $this->assertDatabaseHas('tickets', [
        'title' => 'Notebook não liga',
        'client_id' => $autor->id,
        'status' => 'open',
        'priority' => 'high',
        'technician_id' => null,
    ]);
});

it('recusa abrir chamado sem os campos obrigatórios', function () {
    $this->actingAs(cliente())
        ->post(route('tickets.store'), [])
        ->assertSessionHasErrors(['title', 'description']);

    $this->assertDatabaseCount('tickets', 0);
});

it('preenche a data de resolução ao marcar o chamado como resolvido', function () {
    $tecnico = tecnico();
    $chamado = Ticket::factory()->inProgress($tecnico)->create(['resolved_at' => null]);

    $this->actingAs($tecnico)->put(route('tickets.update', $chamado), [
        'status' => 'resolved',
        'technician_id' => $tecnico->id,
    ]);

    expect($chamado->fresh()->resolved_at)->not->toBeNull();
});

it('mantém a data de resolução original ao reabrir e resolver de novo', function () {
    $tecnico = tecnico();
    $resolvidoEm = now()->subDays(3);
    $chamado = Ticket::factory()->inProgress($tecnico)->create([
        'status' => 'resolved',
        'resolved_at' => $resolvidoEm,
    ]);

    $this->actingAs($tecnico)->put(route('tickets.update', $chamado), [
        'status' => 'resolved',
        'technician_id' => $tecnico->id,
    ]);

    expect($chamado->fresh()->resolved_at->timestamp)
        ->toBe($resolvidoEm->timestamp);
});

it('recusa um status fora do ciclo de vida previsto', function () {
    $tecnico = tecnico();
    $chamado = Ticket::factory()->inProgress($tecnico)->create();

    $this->actingAs($tecnico)
        ->put(route('tickets.update', $chamado), ['status' => 'cancelado'])
        ->assertSessionHasErrors('status');
});

it('filtra os chamados por status', function () {
    $admin = administrador();
    Ticket::factory()->create(['title' => 'Chamado aberto', 'status' => 'open']);
    Ticket::factory()->closed()->create(['title' => 'Chamado fechado']);

    $this->actingAs($admin)
        ->get(route('tickets.index', ['status' => 'open']))
        ->assertSee('Chamado aberto')
        ->assertDontSee('Chamado fechado');
});

it('filtra os chamados por prioridade', function () {
    $admin = administrador();
    Ticket::factory()->priority('urgent')->create(['title' => 'Servidor fora do ar']);
    Ticket::factory()->priority('low')->create(['title' => 'Trocar papel de parede']);

    $this->actingAs($admin)
        ->get(route('tickets.index', ['priority' => 'urgent']))
        ->assertSee('Servidor fora do ar')
        ->assertDontSee('Trocar papel de parede');
});

it('busca chamados pelo código do protocolo', function () {
    $admin = administrador();
    $alvo = Ticket::factory()->create(['title' => 'Alvo da busca']);
    Ticket::factory()->create(['title' => 'Fora da busca']);

    $this->actingAs($admin)
        ->get(route('tickets.index', ['search' => $alvo->code]))
        ->assertSee('Alvo da busca')
        ->assertDontSee('Fora da busca');
});
