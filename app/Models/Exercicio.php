<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Exercicio extends Model
{
    protected $table = 'exercicios';

    protected $fillable = [
        'nome',
        'descricao',
        'series',
        'repeticoes',
        'peso_padrao',
        'dia_semana',
        'ordem',
        'ativo',
        'foto_aparelho',
    ];

    protected $casts = [
        'series'      => 'integer',
        'repeticoes'  => 'integer',
        'ordem'       => 'integer',
        'peso_padrao' => 'float',
        'ativo'       => 'boolean',
    ];

    protected static function booted(): void
    {
        // Ao trocar ou remover a foto, apaga o arquivo antigo da pasta
        static::updating(function (Exercicio $exercicio) {
            if ($exercicio->isDirty('foto_aparelho')) {
                $antiga = $exercicio->getOriginal('foto_aparelho');

                if ($antiga) {
                    Storage::disk('public')->delete($antiga);
                }
            }
        });

        // Ao excluir o exercício, apaga a foto junto
        static::deleting(function (Exercicio $exercicio) {
            if ($exercicio->foto_aparelho) {
                Storage::disk('public')->delete($exercicio->foto_aparelho);
            }
        });
    }

    /**
     * Relação com as execuções
     */
    public function execucoes(): HasMany
    {
        return $this->hasMany(Execucao::class, 'exercicio_id');
    }

    /**
     * URL da foto para exibir na tela
     */
    public function getFotoAparelhoUrlAttribute()
    {
        if (empty($this->foto_aparelho)) {
            return null;
        }

        return asset('storage/' . $this->foto_aparelho);
    }
}