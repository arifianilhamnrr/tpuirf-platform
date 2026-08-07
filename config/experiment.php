<?php

return [
    'protocol_version' => env('EXPERIMENT_PROTOCOL_VERSION', 'TPUIRF-PREF-v4'),
    'consent_version' => env('EXPERIMENT_CONSENT_VERSION', '1.0'),
    'idle_threshold_seconds' => (int) env('EXPERIMENT_IDLE_THRESHOLD_SECONDS', 30),
    'max_event_batch_size' => (int) env('EXPERIMENT_MAX_EVENT_BATCH_SIZE', 100),

    'tasks' => [
        'theme' => [
            'label' => 'Preferensi Tema',
            'options' => ['light', 'dark'],
        ],
        'typography' => [
            'label' => 'Preferensi Tipografi',
            'options' => [
                'font_family' => ['sans', 'serif', 'high_legibility'],
                'font_size' => ['small', 'medium', 'large'],
            ],
        ],
        'layout_density' => [
            'label' => 'Preferensi Kepadatan Tata Letak',
            'options' => ['compact', 'comfortable', 'spacious'],
        ],
        'navigation_position' => [
            'label' => 'Preferensi Posisi Navigasi',
            'options' => ['top', 'left', 'bottom'],
        ],
        'icon_style' => [
            'label' => 'Preferensi Gaya Ikon',
            'options' => ['outline', 'filled', 'duotone'],
        ],
        'design_style' => [
            'label' => 'Preferensi Gaya Desain Akhir',
            'selection_mode' => 'multiple',
            'options' => [
                'minimal_flat',
                'material_elevated',
                'glassmorphism',
                'neumorphism',
                'high_contrast_brutalist',
            ],
        ],
    ],

    'event_types' => [
        'experiment_started',
        'task_viewed',
        'component_clicked',
        'selection_changed',
        'style_preview_expanded',
        'style_preview_collapsed',
        'style_preview_fullscreened',
        'scroll_started',
        'scroll_ended',
        'task_submitted',
        'task_completed',
        'task_revised',
        'evaluation_submitted',
        'experiment_completed',
        'page_hidden',
    ],

    'input_methods' => ['mouse', 'touch', 'keyboard', 'pen', 'unknown'],
];
