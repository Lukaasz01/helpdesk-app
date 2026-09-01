<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

/**
 * Regras de acesso a um chamado.
 *
 * A listagem já filtra os chamados por papel, mas isso sozinho não protege
 * nada: bastava trocar o id na URL para abrir ou alterar o chamado de outra
 * pessoa. As regras abaixo são a barreira real, aplicada por registro.
 */
class TicketPolicy
{
    /**
     * O administrador enxerga e altera qualquer chamado.
     *
     * Retornar null (em vez de false) deixa a decisão seguir para o método
     * específico quando o usuário não é admin.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    /**
     * Cliente vê o que abriu; técnico vê o que lhe foi atribuído.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->client_id
            || $user->id === $ticket->technician_id;
    }

    /**
     * Qualquer usuário autenticado pode abrir um chamado.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Só o técnico responsável altera status e atribuição.
     *
     * O cliente acompanha o próprio chamado, mas não muda o andamento dele —
     * caso contrário poderia se declarar atendido ou reatribuir o técnico.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->technician_id;
    }

    /**
     * Comenta quem participa do chamado: o cliente ou o técnico responsável.
     */
    public function comment(User $user, Ticket $ticket): bool
    {
        return $user->id === $ticket->client_id
            || $user->id === $ticket->technician_id;
    }

    /**
     * Exclusão fica restrita ao administrador, resolvida no before().
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return false;
    }
}
