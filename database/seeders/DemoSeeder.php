<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Popula o sistema com dados de demonstração.
 *
 * Serve para avaliar o projeto sem precisar cadastrar nada à mão: cria os três
 * papéis, um usuário para cada um (senha "password") e chamados em todos os
 * estágios do ciclo de vida, com comentários.
 *
 *     php artisan migrate:fresh --seed --seeder=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['admin', 'technician', 'client'] as $papel) {
            Role::findOrCreate($papel);
        }

        $admin = $this->criarUsuario('Ana Administradora', 'admin@helpdesk.test', 'admin');
        $tecnico = $this->criarUsuario('Bruno Técnico', 'tecnico@helpdesk.test', 'technician');
        $tecnica = $this->criarUsuario('Carla Técnica', 'carla@helpdesk.test', 'technician');
        $cliente = $this->criarUsuario('Diego Cliente', 'cliente@helpdesk.test', 'client');
        $clienta = $this->criarUsuario('Elisa Cliente', 'elisa@helpdesk.test', 'client');

        $chamados = [
            ['Notebook não liga', 'Aperto o botão e nenhuma luz acende. Já testei outra tomada.', $cliente, null, 'open', 'high', null],
            ['Impressora sem tinta', 'A impressora do segundo andar está imprimindo em branco.', $clienta, null, 'open', 'low', null],
            ['Sem acesso ao sistema financeiro', 'Recebo "usuário sem permissão" desde a atualização de ontem.', $cliente, $tecnico, 'in_progress', 'urgent', null],
            ['Internet oscilando na sala 12', 'A conexão cai por alguns segundos a cada meia hora.', $clienta, $tecnica, 'in_progress', 'medium', null],
            ['Trocar monitor com defeito', 'O monitor apresenta linhas verticais na lateral direita.', $cliente, $tecnico, 'resolved', 'medium', now()->subDays(2)],
            ['Instalar pacote Office', 'Preciso do Office na máquina nova do setor.', $clienta, $tecnica, 'closed', 'low', now()->subDays(9)],
        ];

        foreach ($chamados as [$titulo, $descricao, $dono, $responsavel, $status, $prioridade, $resolvidoEm]) {
            $chamado = Ticket::create([
                'code' => Ticket::gerarCodigo(),
                'title' => $titulo,
                'description' => $descricao,
                'client_id' => $dono->id,
                'technician_id' => $responsavel?->id,
                'status' => $status,
                'priority' => $prioridade,
                'resolved_at' => $resolvidoEm,
            ]);

            if ($responsavel) {
                Comment::create([
                    'ticket_id' => $chamado->id,
                    'user_id' => $responsavel->id,
                    'content' => 'Chamado recebido, já estou verificando.',
                ]);

                Comment::create([
                    'ticket_id' => $chamado->id,
                    'user_id' => $dono->id,
                    'content' => 'Obrigado pelo retorno!',
                ]);
            }
        }

        $this->command->newLine();
        $this->command->info('Usuários de demonstração criados (senha: password)');
        $this->command->table(
            ['Papel', 'E-mail'],
            [
                ['Administrador', $admin->email],
                ['Técnico', $tecnico->email],
                ['Técnica', $tecnica->email],
                ['Cliente', $cliente->email],
                ['Cliente', $clienta->email],
            ]
        );
    }

    private function criarUsuario(string $nome, string $email, string $papel): User
    {
        $usuario = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $nome,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $usuario->syncRoles([$papel]);

        return $usuario;
    }
}
