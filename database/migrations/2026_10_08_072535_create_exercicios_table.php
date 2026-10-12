<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercicios', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->text('descricao')->nullable();
            $table->integer('series')->default(3);
            $table->integer('repeticoes')->default(10);
            $table->decimal('peso_padrao', 5, 2)->nullable();
            $table->enum('dia_semana', ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo']);
            $table->integer('ordem')->default(1);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercicios');
    }
};