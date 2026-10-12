<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Historico extends Model
{
    protected $table = 'historico';
    public $timestamps = false; // Tabela possui apenas created_at customizado

    protected $fillable = [
        'sessao_id',
        'total_exercicios',
        'concluidos',
        'observacao',
    ];

    public function sessaoTreino(): BelongsTo
    {
        return $this->belongsTo(SessaoTreino::class, 'sessao_id');
    }
}