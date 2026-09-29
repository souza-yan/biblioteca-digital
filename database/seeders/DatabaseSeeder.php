<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        $users = [
            ['name' => 'Admin',         'email' => 'administrador@administrador.com',     'role' => Role::ADMIN],
            ['name' => 'Gestão',   'email' => 'gestao@gestao.com',    'role' => Role::STAFF],
            ['name' => 'Professor', 'email' => 'professor@professor.com', 'role' => Role::TEACHER],
        ];

        $password = env('SEED_PASSWORD', 'password');

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($password),
                    'role' => $data['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
