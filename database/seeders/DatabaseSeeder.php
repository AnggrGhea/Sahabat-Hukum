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
        // 1. Admin default
        User::firstOrCreate(
            ['email' => 'admin@sahabathukum.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password123'),
                'role'     => 'admin',
                'status'   => 'aktif',
            ]
        );

        // 2. Advokat default
        $advokat = User::firstOrCreate(
            ['email' => 'andi@sahabathukum.com'],
            [
                'name'     => 'Andi Wijaya, S.H.',
                'password' => Hash::make('password123'),
                'role'     => 'advokat',
                'status'   => 'aktif',
            ]
        );
        
        LawyerProfile::firstOrCreate(
            ['user_id' => $advokat->id],
            [
                'specialization' => 'Hukum Pidana',
                'phone'          => '081122334455',
            ]
        );

        // 3. Klien default
        $klien = User::firstOrCreate(
            ['email' => 'budi@email.com'],
            [
                'name'     => 'Budi Santoso',
                'password' => Hash::make('password123'),
                'role'     => 'klien',
                'status'   => 'aktif',
            ]
        );

        ClientProfile::firstOrCreate(
            ['user_id' => $klien->id],
            [
                'phone'   => '081234567890',
                'address' => 'Jl. Merdeka No. 10',
            ]
        );

        // 4. Panggil seeder demo lengkap
        $this->call(DemoUserSeeder::class);
    }
}
