<?php

use App\Models\Ticket;

it('exibe o painel para usuário autenticado e verificado', function () {
    $this->actingAs(cliente())
        ->get(route('dashboard'))
        ->assertOk();
});

it('redireciona visitante não autenticado para o login', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('mostra ao cliente somente os próprios chamados no painel', function () {
    $dono = cliente();
    Ticket::factory()->ownedBy($dono)->create(['title' => 'Chamado do painel']);
    Ticket::factory()->create(['title' => 'Chamado de terceiro']);

    $this->actingAs($dono)
        ->get(route('dashboard'))
        ->assertSee('Chamado do painel')
        ->assertDontSee('Chamado de terceiro');
});
