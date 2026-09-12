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
            ['email' => 'sofian@dimas.israel'],
            [
                'name'     => 'Admin S-Tech',
                'email'    => 'sofian@dimas.israel',
                'password' => Hash::make('EddyMaryanto321'),
                'role'     => 'admin',
            ]
        );

        $this->command->info('✓ Admin account created: sofian@dimas.israel / EddyMaryanto321');
    }
}
