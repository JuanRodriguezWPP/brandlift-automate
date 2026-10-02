<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Camilo Serrato',
                'email' => 'camilo.serrato@wppmedia.com',
                'role' => 'admin',
                'status' => 'active',
                'markets' => null,
            ],
            [
                'name' => 'Sandra Gordillo',
                'email' => 'sandra.gordillo@wppmedia.com',
                'role' => 'admin',
                'status' => 'active',
                'markets' => null,
            ],
            [
                'name' => 'Juan Rodriguez',
                'email' => 'juan.rodriguezv@wppmedia.com',
                'role' => 'admin',
                'status' => 'active',
                'markets' => null,
            ],
        ];

        foreach ($admins as $adminData) {
            User::updateOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'role' => $adminData['role'],
                    'status' => $adminData['status'],
                    'markets' => $adminData['markets'],
                    'login_token' => Str::random(32),
                ]
            );
        }
    }
}
