<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sessoes_treino', function (Blueprint $table) {
            $table->id();
            $table->date('data_treino')->unique();
            $table->boolean('concluido')->default(false);
            $table->timestamp('inicio')->useCurrent();
            $table->timestamp('fim')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessoes_treino');
    }
};