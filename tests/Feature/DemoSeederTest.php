<?php

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\DemoSeeder;

it('popula a base de demonstração com papéis, usuários e chamados', function () {
    $this->seed(DemoSeeder::class);

    expect(User::count())->toBe(5)
        ->and(Ticket::count())->toBe(6)
        ->and(Comment::count())->toBeGreaterThan(0);

    expect(User::role('admin')->count())->toBe(1)
        ->and(User::role('technician')->count())->toBe(2)
        ->and(User::role('client')->count())->toBe(2);
});

it('cria chamados em todos os estágios do ciclo de vida', function () {
    $this->seed(DemoSeeder::class);

    expect(Ticket::pluck('status')->unique()->sort()->values()->all())
        ->toBe(['closed', 'in_progress', 'open', 'resolved']);
});

it('gera códigos de protocolo únicos e sequenciais', function () {
    $this->seed(DemoSeeder::class);

    $codigos = Ticket::orderBy('id')->pluck('code');

    expect($codigos->unique())->toHaveCount(6)
        ->and($codigos->first())->toBe('OS-'.date('Y').'-0001')
        ->and($codigos->last())->toBe('OS-'.date('Y').'-0006');
});

it('pode ser executado duas vezes sem duplicar usuários', function () {
    $this->seed(DemoSeeder::class);
    $this->seed(DemoSeeder::class);

    expect(User::count())->toBe(5);
});
