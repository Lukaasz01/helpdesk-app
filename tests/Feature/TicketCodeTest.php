<?php

use App\Models\Ticket;

it('gera o primeiro código do ano no formato de protocolo', function () {
    expect(Ticket::gerarCodigo())->toBe('OS-'.date('Y').'-0001');
});

it('incrementa o sequencial a cada novo chamado', function () {
    $autor = cliente();

    foreach (['Primeiro', 'Segundo', 'Terceiro'] as $titulo) {
        $this->actingAs($autor)->post(route('tickets.store'), [
            'title' => $titulo,
            'description' => 'Descrição do chamado de teste.',
            'priority' => 'medium',
        ]);
    }

    $ano = date('Y');

    expect(Ticket::orderBy('id')->pluck('code')->all())
        ->toBe(["OS-{$ano}-0001", "OS-{$ano}-0002", "OS-{$ano}-0003"]);
});

it('não repete código entre chamados', function () {
    $autor = cliente();

    for ($i = 0; $i < 15; $i++) {
        $this->actingAs($autor)->post(route('tickets.store'), [
            'title' => "Chamado {$i}",
            'description' => 'Descrição do chamado de teste.',
            'priority' => 'low',
        ]);
    }

    $codigos = Ticket::pluck('code');

    expect($codigos)->toHaveCount(15)
        ->and($codigos->unique())->toHaveCount(15);
});
