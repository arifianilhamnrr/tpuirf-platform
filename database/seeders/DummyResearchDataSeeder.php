<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Models\Experiment;
use App\Infrastructure\Persistence\Models\ExperimentTask;
use App\Infrastructure\Persistence\Models\InteractionEvent;
use App\Infrastructure\Persistence\Models\ParticipantPreference;
use App\Infrastructure\Persistence\Models\TrustEvaluation;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyResearchDataSeeder extends Seeder
{
    public function run(): void
    {
        $dummyProfiles = [
            [
                'name' => 'Budi Santoso', 'gender' => 'male', 'age' => 21,
                'education' => 'bachelor', 'occupation' => 'Mahasiswa', 'digital_literacy' => 5,
                'device_type' => 'laptop', 'visual_preference' => 'minimal', 'context_environment' => 'home',
                'ai_familiarity' => 4, 'daily_device_usage' => '6_plus', 'device_usage_time' => 'night', 'primary_device_purpose' => 'coding',
                'theme' => 'dark', 'color_palette' => 'blue', 'font_family' => 'sans', 'font_size' => 'medium',
                'layout_density' => 'compact', 'navigation_position' => 'left', 'icon_style' => 'outline', 'design_styles' => ['minimal_flat'],
            ],
            [
                'name' => 'Siti Nurhaliza', 'gender' => 'female', 'age' => 23,
                'education' => 'bachelor', 'occupation' => 'Desainer UI/UX', 'digital_literacy' => 5,
                'device_type' => 'desktop', 'visual_preference' => 'aesthetic', 'context_environment' => 'office',
                'ai_familiarity' => 4, 'daily_device_usage' => '6_plus', 'device_usage_time' => 'afternoon', 'primary_device_purpose' => 'design',
                'theme' => 'light', 'color_palette' => 'purple', 'font_family' => 'sans', 'font_size' => 'medium',
                'layout_density' => 'comfortable', 'navigation_position' => 'top', 'icon_style' => 'duotone', 'design_styles' => ['glassmorphism'],
            ],
            [
                'name' => 'Ahmad Fauzi', 'gender' => 'male', 'age' => 20,
                'education' => 'high_school', 'occupation' => 'Mahasiswa', 'digital_literacy' => 3,
                'device_type' => 'smartphone', 'visual_preference' => 'simple', 'context_environment' => 'school',
                'ai_familiarity' => 2, 'daily_device_usage' => '3_5', 'device_usage_time' => 'evening', 'primary_device_purpose' => 'social_media',
                'theme' => 'light', 'color_palette' => 'green', 'font_family' => 'sans', 'font_size' => 'large',
                'layout_density' => 'spacious', 'navigation_position' => 'bottom', 'icon_style' => 'filled', 'design_styles' => ['material_elevated'],
            ],
            [
                'name' => 'Dewi Lestari', 'gender' => 'female', 'age' => 28,
                'education' => 'master', 'occupation' => 'Peneliti', 'digital_literacy' => 4,
                'device_type' => 'laptop', 'visual_preference' => 'academic', 'context_environment' => 'office',
                'ai_familiarity' => 3, 'daily_device_usage' => '6_plus', 'device_usage_time' => 'morning', 'primary_device_purpose' => 'productivity',
                'theme' => 'light', 'color_palette' => 'neutral', 'font_family' => 'serif', 'font_size' => 'large',
                'layout_density' => 'comfortable', 'navigation_position' => 'top', 'icon_style' => 'outline', 'design_styles' => ['minimal_flat'],
            ],
            [
                'name' => 'Rizky Pratama', 'gender' => 'male', 'age' => 22,
                'education' => 'bachelor', 'occupation' => 'Software Engineer', 'digital_literacy' => 5,
                'device_type' => 'laptop', 'visual_preference' => 'high_contrast', 'context_environment' => 'coffee_shop',
                'ai_familiarity' => 5, 'daily_device_usage' => '6_plus', 'device_usage_time' => 'night', 'primary_device_purpose' => 'coding',
                'theme' => 'dark', 'color_palette' => 'orange', 'font_family' => 'high_legibility', 'font_size' => 'small',
                'layout_density' => 'compact', 'navigation_position' => 'left', 'icon_style' => 'duotone', 'design_styles' => ['high_contrast_brutalist'],
            ],
            [
                'name' => 'Putri Ayu', 'gender' => 'female', 'age' => 24,
                'education' => 'bachelor', 'occupation' => 'Karyawan Swasta', 'digital_literacy' => 4,
                'device_type' => 'smartphone', 'visual_preference' => 'soft', 'context_environment' => 'home',
                'ai_familiarity' => 3, 'daily_device_usage' => '3_5', 'device_usage_time' => 'evening', 'primary_device_purpose' => 'entertainment',
                'theme' => 'dark', 'color_palette' => 'rose', 'font_family' => 'sans', 'font_size' => 'medium',
                'layout_density' => 'comfortable', 'navigation_position' => 'bottom', 'icon_style' => 'filled', 'design_styles' => ['neumorphism'],
            ],
            [
                'name' => 'Fajar Nugroho', 'gender' => 'male', 'age' => 26,
                'education' => 'bachelor', 'occupation' => 'Data Analyst', 'digital_literacy' => 5,
                'device_type' => 'desktop', 'visual_preference' => 'clean', 'context_environment' => 'office',
                'ai_familiarity' => 4, 'daily_device_usage' => '6_plus', 'device_usage_time' => 'afternoon', 'primary_device_purpose' => 'productivity',
                'theme' => 'dark', 'color_palette' => 'blue', 'font_family' => 'high_legibility', 'font_size' => 'medium',
                'layout_density' => 'compact', 'navigation_position' => 'top', 'icon_style' => 'outline', 'design_styles' => ['material_elevated'],
            ],
            [
                'name' => 'Anisa Rahmawati', 'gender' => 'female', 'age' => 21,
                'education' => 'bachelor', 'occupation' => 'Mahasiswa', 'digital_literacy' => 4,
                'device_type' => 'tablet', 'visual_preference' => 'modern', 'context_environment' => 'home',
                'ai_familiarity' => 3, 'daily_device_usage' => '3_5', 'device_usage_time' => 'afternoon', 'primary_device_purpose' => 'study',
                'theme' => 'light', 'color_palette' => 'purple', 'font_family' => 'sans', 'font_size' => 'medium',
                'layout_density' => 'comfortable', 'navigation_position' => 'left', 'icon_style' => 'duotone', 'design_styles' => ['glassmorphism'],
            ],
            [
                'name' => 'Hendra Setiawan', 'gender' => 'male', 'age' => 29,
                'education' => 'master', 'occupation' => 'Dosen / Peneliti', 'digital_literacy' => 4,
                'device_type' => 'laptop', 'visual_preference' => 'structured', 'context_environment' => 'office',
                'ai_familiarity' => 4, 'daily_device_usage' => '6_plus', 'device_usage_time' => 'morning', 'primary_device_purpose' => 'research',
                'theme' => 'light', 'color_palette' => 'neutral', 'font_family' => 'serif', 'font_size' => 'medium',
                'layout_density' => 'spacious', 'navigation_position' => 'top', 'icon_style' => 'outline', 'design_styles' => ['minimal_flat'],
            ],
            [
                'name' => 'Nadia Safitri', 'gender' => 'female', 'age' => 22,
                'education' => 'bachelor', 'occupation' => 'Frontend Developer', 'digital_literacy' => 5,
                'device_type' => 'laptop', 'visual_preference' => 'bold', 'context_environment' => 'coffee_shop',
                'ai_familiarity' => 5, 'daily_device_usage' => '6_plus', 'device_usage_time' => 'night', 'primary_device_purpose' => 'coding',
                'theme' => 'dark', 'color_palette' => 'orange', 'font_family' => 'sans', 'font_size' => 'small',
                'layout_density' => 'compact', 'navigation_position' => 'left', 'icon_style' => 'duotone', 'design_styles' => ['high_contrast_brutalist'],
            ],
        ];

        foreach ($dummyProfiles as $idx => $d) {
            $email = 'dummy_respondent_' . ($idx + 1) . '@tpuirf.test';
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $d['name'],
                    'password' => bcrypt('password123'),
                    'role' => 'respondent',
                    'date_of_birth' => now()->subYears($d['age'])->format('Y-m-d'),
                    'is_active' => true,
                ]
            );

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'age' => $d['age'],
                    'gender' => $d['gender'],
                    'education' => $d['education'],
                    'occupation' => $d['occupation'],
                    'digital_literacy' => $d['digital_literacy'],
                    'device_type' => $d['device_type'],
                    'visual_preference' => $d['visual_preference'],
                    'context_environment' => $d['context_environment'],
                    'ai_familiarity' => $d['ai_familiarity'],
                    'daily_device_usage' => $d['daily_device_usage'],
                    'device_usage_time' => $d['device_usage_time'],
                    'primary_device_purpose' => $d['primary_device_purpose'],
                ]
            );

            $experiment = Experiment::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'status' => 'completed',
                    'protocol_version' => config('experiment.protocol_version', 'TPUIRF-PREF-v4'),
                    'current_task' => 'design_style',
                    'ui_order' => ['A', 'B', 'C', 'D'],
                    'current_step' => 6,
                    'current_ui_version' => 'D',
                    'preferred_ui' => $d['design_styles'][0],
                    'total_active_duration_ms' => rand(45000, 95000),
                    'started_at' => now()->subHours(2),
                    'completed_at' => now()->subHours(1),
                ]
            );

            $tasks = [
                'theme' => $d['theme'],
                'typography' => ['font_family' => $d['font_family'], 'font_size' => $d['font_size']],
                'layout_density' => $d['layout_density'],
                'navigation_position' => $d['navigation_position'],
                'icon_style' => $d['icon_style'],
                'design_style' => ['design_styles' => $d['design_styles']],
            ];

            $seq = 1;
            foreach ($tasks as $tKey => $tVal) {
                ExperimentTask::updateOrCreate(
                    [
                        'experiment_id' => $experiment->id,
                        'task_key' => $tKey,
                    ],
                    [
                        'sequence' => $seq++,
                        'status' => 'completed',
                        'option_order' => ['opt1', 'opt2'],
                        'selected_option' => is_string($tVal) ? $tVal : null,
                        'selection_payload' => is_array($tVal) ? $tVal : null,
                        'active_duration_ms' => rand(7000, 15000),
                        'idle_duration_ms' => rand(500, 2000),
                        'change_count' => rand(0, 2),
                        'started_at' => now()->subHours(2),
                        'completed_at' => now()->subHours(1),
                    ]
                );
            }

            ParticipantPreference::updateOrCreate(
                ['experiment_id' => $experiment->id],
                [
                    'user_id' => $user->id,
                    'theme' => $d['theme'],
                    'color_palette' => $d['color_palette'],
                    'font_family' => $d['font_family'],
                    'font_size' => $d['font_size'],
                    'layout_density' => $d['layout_density'],
                    'navigation_position' => $d['navigation_position'],
                    'icon_style' => $d['icon_style'],
                    'design_style' => implode('|', $d['design_styles']),
                    'final_config' => [
                        'theme' => $d['theme'],
                        'color_palette' => $d['color_palette'],
                        'font_family' => $d['font_family'],
                        'font_size' => $d['font_size'],
                        'layout_density' => $d['layout_density'],
                        'navigation_position' => $d['navigation_position'],
                        'icon_style' => $d['icon_style'],
                        'design_styles' => $d['design_styles'],
                    ],
                ]
            );

            TrustEvaluation::updateOrCreate(
                ['experiment_id' => $experiment->id],
                [
                    'user_id' => $user->id,
                    'trust_score' => rand(4, 5),
                    'transparency_score' => rand(4, 5),
                    'ui_satisfaction' => rand(4, 5),
                    'ai_usage_frequency' => 'daily',
                    'ai_answer_trust' => rand(3, 5),
                    'ai_explanation_preference' => rand(4, 5),
                    'feedback' => 'Penelitian antarmuka yang sangat menarik dan intuitif.',
                ]
            );

            // Seed sample interaction events
            for ($ev = 1; $ev <= 8; $ev++) {
                InteractionEvent::create([
                    'experiment_id' => $experiment->id,
                    'task_key' => 'design_style',
                    'sequence_number' => $ev,
                    'event_type' => $ev % 2 === 0 ? 'component_clicked' : 'component_hovered',
                    'component_id' => 'btn-style-' . ($ev % 3),
                    'option_id' => $d['design_styles'][0],
                    'client_timestamp' => now()->toISOString(),
                    'server_received_at' => now(),
                    'elapsed_task_ms' => $ev * 1200,
                    'elapsed_experiment_ms' => $ev * 3000,
                    'input_method' => 'pointer',
                    'payload' => ['x' => rand(100, 800), 'y' => rand(100, 600)],
                ]);
            }
        }
    }
}
