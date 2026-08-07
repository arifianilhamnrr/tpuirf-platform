<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Models\UiTemplate;
use Illuminate\Database\Seeder;

class UiTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'version' => 'A',
                'name' => 'Minimal Light',
                'description' => 'Clean, minimal interface with light background and simple typography.',
                'is_active' => true,
                'config' => [
                    'theme' => 'light',
                    'layout' => 'single-column',
                    'font_size' => 'medium',
                    'navigation' => 'top',
                    'border_radius' => 'small',
                    'color_scheme' => 'neutral',
                    'density' => 'comfortable',
                ],
            ],
            [
                'version' => 'B',
                'name' => 'Vibrant Colorful',
                'description' => 'Bold, colorful interface with vibrant accents and modern design.',
                'is_active' => true,
                'config' => [
                    'theme' => 'light',
                    'layout' => 'grid',
                    'font_size' => 'medium',
                    'navigation' => 'side',
                    'border_radius' => 'large',
                    'color_scheme' => 'vibrant',
                    'density' => 'compact',
                ],
            ],
            [
                'version' => 'C',
                'name' => 'Dark Glass',
                'description' => 'Dark mode interface with glassmorphism effects and elegant styling.',
                'is_active' => true,
                'config' => [
                    'theme' => 'dark',
                    'layout' => 'single-column',
                    'font_size' => 'large',
                    'navigation' => 'bottom',
                    'border_radius' => 'full',
                    'color_scheme' => 'dark',
                    'density' => 'comfortable',
                ],
            ],
            [
                'version' => 'D',
                'name' => 'Modern Compact',
                'description' => 'Modern, dense interface optimized for power users with advanced controls.',
                'is_active' => true,
                'config' => [
                    'theme' => 'light',
                    'layout' => 'multi-column',
                    'font_size' => 'small',
                    'navigation' => 'top-compact',
                    'border_radius' => 'medium',
                    'color_scheme' => 'professional',
                    'density' => 'compact',
                ],
            ],
        ];

        foreach ($templates as $template) {
            UiTemplate::create($template);
        }
    }
}
