<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@stech.id'],
            [
                'name'     => 'Admin S-Tech',
                'email'    => 'admin@stech.id',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
            ]
        );

        $this->command->info('✓ Admin account created: admin@stech.id / admin123');
    }
}
