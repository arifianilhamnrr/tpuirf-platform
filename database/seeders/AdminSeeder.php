<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin TPUIRF',
            'email' => 'admin@tpuirf.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'date_of_birth' => '1990-01-01',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Researcher 1',
            'email' => 'researcher@tpuirf.com',
            'password' => Hash::make('researcher123'),
            'role' => 'researcher',
            'date_of_birth' => '1995-06-15',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('respondent123'),
            'role' => 'respondent',
            'date_of_birth' => '2000-03-20',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
