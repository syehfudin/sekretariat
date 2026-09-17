<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoOrgSeeder extends Seeder
{
    /**
     * Struktur demo sesuai requirement:
     * - Sekre C2 & Ka C2 membawahi user Sekre C1 & Ka C1 (sama-sama lihat anggota C2)
     * - SekC1 (Sekre) bisa input, KaGWB (Ka C1) hanya lihat
     * - C1 & C2 share anggota
     */
    public function run(): void
    {
        $roles = Role::pluck('id', 'kode');

        // User C2
        $sekreC2 = User::updateOrCreate(
            ['username' => 'SekC2'],
            [
                'nama_lengkap' => 'Sekretaris C2',
                'name' => 'SekC2',
                'email' => 'sekc2@sekretariat.local',
                'password' => Hash::make('12345'),
                'role_id' => $roles['sekre_c2'],
            ]
        );

        $kaC2 = User::updateOrCreate(
            ['username' => 'KaC2'],
            [
                'nama_lengkap' => 'Kepala C2',
                'name' => 'KaC2',
                'email' => 'kac2@sekretariat.local',
                'password' => Hash::make('12345'),
                'role_id' => $roles['ka_c2'],
            ]
        );

        // User C1
        $sekreC1 = User::updateOrCreate(
            ['username' => 'SekGWB'],
            [
                'nama_lengkap' => 'Sekretaris GWB',
                'name' => 'SekGWB',
                'email' => 'sekgwb@sekretariat.local',
                'password' => Hash::make('12345'),
                'role_id' => $roles['sekre_c1'],
            ]
        );

        $kaC1 = User::updateOrCreate(
            ['username' => 'KaGWB'],
            [
                'nama_lengkap' => 'Kepala GWB',
                'name' => 'KaGWB',
                'email' => 'kagwb@sekretariat.local',
                'password' => Hash::make('12345'),
                'role_id' => $roles['ka_c1'],
            ]
        );

        // Anggota contoh
        $anggotaData = [
            ['nama_lengkap' => 'Budi Santoso', 'pekerjaan' => 'Karyawan Swasta'],
            ['nama_lengkap' => 'Siti Aminah', 'pekerjaan' => 'Guru'],
            ['nama_lengkap' => 'Ahmad Fauzi', 'pekerjaan' => 'Wiraswasta'],
        ];

        foreach ($anggotaData as $data) {
            $anggota = Anggota::firstOrCreate(
                ['nama_lengkap' => $data['nama_lengkap']],
                ['pekerjaan' => $data['pekerjaan']]
            );

            // Anggota dipakai bersama C1 dan C2 (sekre + ka)
            foreach ([$sekreC2, $kaC2, $sekreC1, $kaC1] as $user) {
                $user->anggota()->syncWithoutDetaching([$anggota->id]);
            }
        }
    }
}