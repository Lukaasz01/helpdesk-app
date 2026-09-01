<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Garante que os três papéis da aplicação existem no banco de teste.
 *
 * O spatie/laravel-permission mantém as permissões em cache. Sem limpá-lo,
 * o papel criado em um teste continua em memória no teste seguinte e a
 * verificação de papel passa por engano.
 */
function criarPapeis(): void
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    foreach (['admin', 'technician', 'client'] as $papel) {
        Role::findOrCreate($papel);
    }
}

/**
 * Cria um usuário já com o papel informado.
 */
function usuarioCom(string $papel): User
{
    criarPapeis();

    $usuario = User::factory()->create();
    $usuario->assignRole($papel);

    return $usuario;
}

function cliente(): User
{
    return usuarioCom('client');
}

function tecnico(): User
{
    return usuarioCom('technician');
}

function administrador(): User
{
    return usuarioCom('admin');
}
