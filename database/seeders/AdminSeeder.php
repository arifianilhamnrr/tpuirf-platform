<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@tpuirf.com'],
            [
                'name' => 'Admin TPUIRF',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'date_of_birth' => '1990-01-01',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        User::firstOrCreate(
            ['email' => 'researcher@tpuirf.com'],
            [
                'name' => 'Researcher 1',
                'password' => Hash::make('researcher123'),
                'role' => 'researcher',
                'date_of_birth' => '1995-06-15',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        User::firstOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('respondent123'),
                'role' => 'respondent',
                'date_of_birth' => '2000-03-20',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}
