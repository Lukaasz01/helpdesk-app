<?php

use App\Models\Ticket;

it('permite ao cliente comentar no próprio chamado', function () {
    $dono = cliente();
    $chamado = Ticket::factory()->ownedBy($dono)->create();

    $this->actingAs($dono)
        ->post(route('tickets.comments.store', $chamado), [
            'content' => 'Segue o print do erro.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('comments', [
        'ticket_id' => $chamado->id,
        'user_id' => $dono->id,
        'content' => 'Segue o print do erro.',
    ]);
});

it('permite ao técnico responsável comentar no chamado', function () {
    $tecnico = tecnico();
    $chamado = Ticket::factory()->inProgress($tecnico)->create();

    $this->actingAs($tecnico)
        ->post(route('tickets.comments.store', $chamado), [
            'content' => 'Vou verificar ainda hoje.',
        ])
        ->assertRedirect();

    $this->assertDatabaseCount('comments', 1);
});

it('impede comentário de quem não participa do chamado', function () {
    $intruso = cliente();
    $chamado = Ticket::factory()->create();

    $this->actingAs($intruso)
        ->post(route('tickets.comments.store', $chamado), [
            'content' => 'Comentário indevido.',
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('comments', 0);
});

it('recusa comentário vazio', function () {
    $dono = cliente();
    $chamado = Ticket::factory()->ownedBy($dono)->create();

    $this->actingAs($dono)
        ->post(route('tickets.comments.store', $chamado), ['content' => ''])
        ->assertSessionHasErrors('content');

    $this->assertDatabaseCount('comments', 0);
});

it('exige autenticação para comentar', function () {
    $chamado = Ticket::factory()->create();

    $this->post(route('tickets.comments.store', $chamado), ['content' => 'Oi'])
        ->assertRedirect(route('login'));
});
