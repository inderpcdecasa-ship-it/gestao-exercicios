<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('historico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sessao_id')->constrained('sessoes_treino')->cascadeOnDelete();
            $table->integer('total_exercicios')->default(0);
            $table->integer('concluidos')->default(0);
            $table->text('observacao')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico');
    }
};