<?php

namespace App\Application\ML;

final class ModelDataContract
{
    public const FEATURES = [
        'age',
        'gender',
        'education',
        'occupation',
        'digital_literacy',
        'device_type',
        'visual_preference',
        'context_environment',
        'ai_familiarity',
        'daily_device_usage',
        'device_usage_time',
        'primary_device_purpose',
        'click_count',
        'scroll_count',
        'hover_count',
        'keyboard_count',
        'avg_task_time',
        'total_session_time',
    ];

    public const TARGETS = [
        'theme',
        'color_palette',
        'font_family',
        'font_size',
        'layout_density',
        'navigation_position',
        'icon_style',
        'design_style',
    ];

    private function __construct() {}
}
