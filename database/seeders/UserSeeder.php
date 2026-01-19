<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario Admin
        User::create([
            'name' => 'Admin Perfumería',
            'email' => 'admin@perfumeria.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'phone' => '+51 999 888 777',
            'address' => 'Av. Larco 123, Miraflores, Lima',
        ]);

        // Usuario Cliente de prueba
        User::create([
            'name' => 'Johan Piedra',
            'email' => 'johan@test.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'phone' => '+51 987 654 321',
            'address' => 'Calle Los Olivos 456, San Isidro, Lima',
        ]);

        // Cliente adicional
        User::create([
            'name' => 'María García',
            'email' => 'maria@test.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'phone' => '+51 912 345 678',
            'address' => 'Jr. Los Pinos 789, Surco, Lima',
        ]);
    }
}
