<?php

use App\Models\Ticket;

/*
|--------------------------------------------------------------------------
| Autorização por chamado
|--------------------------------------------------------------------------
|
| A listagem filtra os chamados por papel, mas isso não protege o registro:
| até a introdução da TicketPolicy, bastava trocar o id na URL para ler ou
| alterar o chamado de outra pessoa. Os testes abaixo cobrem exatamente esse
| acesso direto, que é o caminho que um atacante usaria.
|
*/

it('impede um cliente de abrir o chamado de outro cliente', function () {
    $dono = cliente();
    $intruso = cliente();
    $chamado = Ticket::factory()->ownedBy($dono)->create();

    $this->actingAs($intruso)
        ->get(route('tickets.show', $chamado))
        ->assertForbidden();
});

it('permite ao cliente abrir o próprio chamado', function () {
    $dono = cliente();
    $chamado = Ticket::factory()->ownedBy($dono)->create();

    $this->actingAs($dono)
        ->get(route('tickets.show', $chamado))
        ->assertOk();
});

it('impede um técnico de abrir chamado que não lhe foi atribuído', function () {
    $tecnico = tecnico();
    $outro = tecnico();
    $chamado = Ticket::factory()->inProgress($outro)->create();

    $this->actingAs($tecnico)
        ->get(route('tickets.show', $chamado))
        ->assertForbidden();
});

it('permite ao técnico responsável abrir o chamado', function () {
    $tecnico = tecnico();
    $chamado = Ticket::factory()->inProgress($tecnico)->create();

    $this->actingAs($tecnico)
        ->get(route('tickets.show', $chamado))
        ->assertOk();
});

it('permite ao administrador abrir qualquer chamado', function () {
    $admin = administrador();
    $chamado = Ticket::factory()->create();

    $this->actingAs($admin)
        ->get(route('tickets.show', $chamado))
        ->assertOk();
});

it('impede o cliente de alterar o status do próprio chamado', function () {
    $dono = cliente();
    $chamado = Ticket::factory()->ownedBy($dono)->create(['status' => 'open']);

    $this->actingAs($dono)
        ->put(route('tickets.update', $chamado), ['status' => 'resolved'])
        ->assertForbidden();

    expect($chamado->fresh()->status)->toBe('open');
});

it('impede um técnico de alterar chamado de outro técnico', function () {
    $tecnico = tecnico();
    $outro = tecnico();
    $chamado = Ticket::factory()->inProgress($outro)->create(['status' => 'in_progress']);

    $this->actingAs($tecnico)
        ->put(route('tickets.update', $chamado), ['status' => 'resolved'])
        ->assertForbidden();

    expect($chamado->fresh()->status)->toBe('in_progress');
});

it('permite ao técnico responsável alterar o chamado', function () {
    $tecnico = tecnico();
    $chamado = Ticket::factory()->inProgress($tecnico)->create();

    $this->actingAs($tecnico)
        ->put(route('tickets.update', $chamado), [
            'status' => 'resolved',
            'technician_id' => $tecnico->id,
        ])
        ->assertRedirect();

    expect($chamado->fresh()->status)->toBe('resolved');
});

it('exige autenticação para acessar os chamados', function () {
    $chamado = Ticket::factory()->create();

    $this->get(route('tickets.index'))->assertRedirect(route('login'));
    $this->get(route('tickets.show', $chamado))->assertRedirect(route('login'));
});

it('recusa atribuir o chamado a um usuário que não é técnico', function () {
    $tecnico = tecnico();
    $naoTecnico = cliente();
    $chamado = Ticket::factory()->inProgress($tecnico)->create();

    $this->actingAs($tecnico)
        ->put(route('tickets.update', $chamado), [
            'status' => 'in_progress',
            'technician_id' => $naoTecnico->id,
        ])
        ->assertSessionHasErrors('technician_id');
});
