<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\FotoAparelhoController;

Route::get('/', function () {
    return view('welcome');
});

// Evita "Route [login] not defined" quando a sessão expira
Route::get('/login', function () {
    return redirect()->to(filament()->getPanel('admin')->getLoginUrl());
})->name('login');

// Rota para salvar a foto do aparelho
Route::post('/admin/salvar-foto-aparelho', [FotoAparelhoController::class, 'store'])
    ->middleware(['web', 'auth'])
    ->name('salvar-foto-aparelho');