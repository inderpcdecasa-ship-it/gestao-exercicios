<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('execucoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sessao_id')->constrained('sessoes_treino')->cascadeOnDelete();
            $table->foreignId('exercicio_id')->constrained('exercicios')->cascadeOnDelete();
            $table->integer('serie')->default(1);
            $table->integer('repeticoes_feitas')->default(0);
            $table->decimal('peso_utilizado', 5, 2)->nullable();
            $table->boolean('concluido')->default(false);
            $table->timestamps();

            $table->unique(['sessao_id', 'exercicio_id', 'serie'], 'unico_exercicio_serie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('execucoes');
    }
};