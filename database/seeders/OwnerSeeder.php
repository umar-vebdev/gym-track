<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    /**
     * Создаёт учётную запись владельца зала.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'owner@gymtrack.local'],
            [
                'name'     => 'Owner',
                'password' => Hash::make('password'),
            ]
        );
    }
}
