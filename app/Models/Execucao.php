<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Execucao extends Model
{
    protected $table = 'execucoes';

    protected $fillable = [
        'sessao_id',
        'exercicio_id',
        'serie',
        'repeticoes_feitas',
        'peso_utilizado',
        'concluido',
    ];

    protected $casts = [
        'concluido' => 'boolean',
        'peso_utilizado' => 'decimal:2',
        'serie' => 'integer',
        'repeticoes_feitas' => 'integer',
    ];

    public function sessaoTreino(): BelongsTo
    {
        return $this->belongsTo(SessaoTreino::class, 'sessao_id');
    }

    public function exercicio(): BelongsTo
    {
        return $this->belongsTo(Exercicio::class, 'exercicio_id');
    }
}