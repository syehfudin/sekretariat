<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['kode' => 'superadmin', 'nama' => 'Super Admin', 'keterangan' => 'Akses penuh, hanya admin'],
            ['kode' => 'sekre_c1', 'nama' => 'Sekre C1', 'keterangan' => 'Sekretaris C1 - kelola anggota'],
            ['kode' => 'sekre_c2', 'nama' => 'Sekre C2', 'keterangan' => 'Sekretaris C2 - kelola anggota'],
            ['kode' => 'ka_c1', 'nama' => 'Ka C1', 'keterangan' => 'Kepala C1 - lihat data'],
            ['kode' => 'ka_c2', 'nama' => 'Ka C2', 'keterangan' => 'Kepala C2 - lihat data'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['kode' => $role['kode']], $role);
        }

        // Admin = superadmin
        User::where('username', 'admin')->update(['role_id' => Role::where('kode', 'superadmin')->value('id')]);
    }
}