<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Sequência usada para gerar códigos únicos dentro de um mesmo teste.
     */
    protected static int $sequence = 0;

    public function definition(): array
    {
        static::$sequence++;

        return [
            'code' => sprintf('OS-%s-%04d', date('Y'), static::$sequence),
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'client_id' => User::factory(),
            'technician_id' => null,
            'status' => 'open',
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'resolved_at' => null,
        ];
    }

    /**
     * Chamado já atribuído a um técnico e em atendimento.
     */
    public function inProgress(?User $technician = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'technician_id' => $technician?->id ?? User::factory(),
        ]);
    }

    /**
     * Chamado resolvido, com a data de resolução preenchida.
     */
    public function resolved(?User $technician = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'technician_id' => $technician?->id ?? User::factory(),
            'resolved_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'closed']);
    }

    /**
     * Chamado pertencente a um cliente específico.
     */
    public function ownedBy(User $client): static
    {
        return $this->state(fn (array $attributes) => ['client_id' => $client->id]);
    }

    public function priority(string $priority): static
    {
        return $this->state(fn (array $attributes) => ['priority' => $priority]);
    }
}
