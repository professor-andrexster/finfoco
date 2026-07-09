<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // O admin (andrexster@gmail.com) se registra normalmente via /register.
        // Este seeder NUNCA cria o usuário — apenas promove a is_admin=true
        // se ele já existir no banco. Se ainda não se registrou, não faz nada.
        $admin = User::where('email', 'andrexster@gmail.com')->first();

        if ($admin) {
            $admin->update(['is_admin' => true]);
        }
    }
}
