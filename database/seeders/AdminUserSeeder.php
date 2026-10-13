<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'iabsilva@hotmail.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('123456'),
            ]
        );
    }
}