<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Models\Experiment;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Seeder;

class ExperimentSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'john@example.com')->first();
        
        if ($user) {
            Experiment::create([
                'user_id' => $user->id,
                'status' => 'completed',
                'ui_order' => ['A', 'C', 'B', 'D'],
                'current_step' => 4,
                'current_ui_version' => 'D',
                'started_at' => now()->subHours(2),
                'completed_at' => now()->subHour(),
                'preferred_ui' => 'C',
            ]);
        }
    }
}
