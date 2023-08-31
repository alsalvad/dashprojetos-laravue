<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'user' => 'test',
            'password' => Hash::make(123),
            'token_dashboard' => '123',
            'admin' => 1
        ]);

        \App\Models\Grupo::create([
            'titulo' => 'Geral',
            'tipo' => 'notes',
            'user_id' => 1,
            'default' => 1
        ]);
    }
}
