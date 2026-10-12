<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SessaoTreino extends Model
{
    protected $table = 'sessoes_treino';

    protected $fillable = [
        'data_treino',
        'concluido',
        'inicio',
        'fim',
    ];

    public function execucoes(): HasMany
    {
        return $this->hasMany(Execucao::class, 'sessao_id');
    }

    public function historico(): HasOne
    {
        return $this->hasOne(Historico::class, 'sessao_id');
    }
}