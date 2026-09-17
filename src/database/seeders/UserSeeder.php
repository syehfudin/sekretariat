<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'nama_lengkap' => 'Administrator Sekretariat',
                'name' => 'Administrator',
                'email' => 'admin@sekretariat.local',
                'password' => Hash::make('Admin123'),
            ]
        );

        User::updateOrCreate(
            ['username' => 'user'],
            [
                'nama_lengkap' => 'User Standar',
                'name' => 'User',
                'email' => 'user@sekretariat.local',
                'password' => Hash::make('12345'),
            ]
        );
    }
}