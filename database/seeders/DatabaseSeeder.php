<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ClientProfile;
use App\Models\LawyerProfile;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@sahabathukum.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        // 2. Advokat
        $advokat = User::create([
            'name' => 'Andi Wijaya, S.H.',
            'email' => 'andi@sahabathukum.com',
            'password' => Hash::make('password123'),
            'role' => 'advokat',
            'status' => 'aktif',
        ]);
        
        LawyerProfile::create([
            'user_id' => $advokat->id,
            'specialization' => 'Hukum Pidana',
            'phone' => '081122334455',
        ]);

        // 3. Klien
        $klien = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@email.com',
            'password' => Hash::make('password123'),
            'role' => 'klien',
            'status' => 'aktif',
        ]);

        ClientProfile::create([
            'user_id' => $klien->id,
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 10',
        ]);
    }
}
