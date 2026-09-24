<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'juan.rodriguezv@wppmedia.com'],
            [
                'name' => 'Admin',
                'role' => 'admin',
                'password' => null
            ]
        );
    }
}
