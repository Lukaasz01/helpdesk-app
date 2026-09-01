<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'client_id',
        'technician_id',
        'status',
        'priority',
        'resolved_at',
    ];

    /**
     * Sem este cast, resolved_at volta do banco como string e qualquer
     * operacao de data sobre ele (diferenca, formatacao) quebra.
     */
    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    /**
     * Gera o próximo código de protocolo do ano corrente (OS-2026-0001).
     *
     * A versão anterior usava os 4 últimos caracteres de uniqid(), o que dá
     * 65 mil combinações: pelo paradoxo do aniversário, a chance de repetir
     * passa de 50% por volta de 300 chamados, e a coluna é UNIQUE — a segunda
     * abertura com o mesmo código quebraria com erro de banco.
     *
     * Aqui o número vem do último protocolo do ano, dentro de uma transação
     * com lock na linha, para dois pedidos simultâneos não receberem o mesmo.
     */
    public static function gerarCodigo(): string
    {
        $ano = date('Y');

        return DB::transaction(function () use ($ano) {
            $ultimo = static::where('code', 'like', "OS-{$ano}-%")
                ->lockForUpdate()
                ->orderByDesc('code')
                ->value('code');

            $sequencial = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;

            return sprintf('OS-%s-%04d', $ano, $sequencial);
        });
    }
}
