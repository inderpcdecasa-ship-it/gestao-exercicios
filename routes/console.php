<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sistema:backup', function () {
    $dataHora = now()->format('Y-m-d_H-i-s');
    $pastaBackup = storage_path("app/backups/{$dataHora}");

    // Cria a pasta de backup se não existir
    if (!File::exists($pastaBackup)) {
        File::makeDirectory($pastaBackup, 0755, true);
    }

    // 1. Copiar o arquivo do banco de dados SQLite
    $dbPath = database_path('database.sqlite');
    if (File::exists($dbPath)) {
        File::copy($dbPath, "{$pastaBackup}/database.sqlite");
    }

    // 2. Copiar os uploads (fotos dos aparelhos, etc.)
    $publicStorage = storage_path('app/public');
    if (File::isDirectory($publicStorage)) {
        File::copyDirectory($publicStorage, "{$pastaBackup}/public_storage");
    }

    $this->info("Backup gerado com sucesso em: {$pastaBackup}");
})->describe('Faz backup do banco SQLite e dos arquivos de upload');

// Agenda o comando para rodar todos os dias automaticamente
Schedule::command('sistema:backup')->daily();