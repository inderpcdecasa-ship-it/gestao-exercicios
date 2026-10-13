<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        User::updateOrCreate(
            ['email' => 'iabsilva@hotmail.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('123456'),
            ]
        );
    }

    public function down(): void
    {
        //
    }
};