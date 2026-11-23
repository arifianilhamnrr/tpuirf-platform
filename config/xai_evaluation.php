<?php

return [
    'instrument_version' => 'XAI-LIKERT-v1',
    'scale' => [
        1 => 'Sangat Tidak Setuju',
        2 => 'Tidak Setuju',
        3 => 'Netral',
        4 => 'Setuju',
        5 => 'Sangat Setuju',
    ],
    'items' => [
        'perceived_transparency' => [
            'construct' => 'Perceived Transparency',
            'statement' => 'Saya memahami alasan mengapa sistem merekomendasikan konfigurasi antarmuka ini untuk saya.',
        ],
        'system_trust' => [
            'construct' => 'System Trust',
            'statement' => 'Saya percaya bahwa rekomendasi konfigurasi antarmuka yang diberikan sistem sesuai dengan kebutuhan dan karakteristik saya.',
        ],
        'decision_confidence' => [
            'construct' => 'Decision Confidence',
            'statement' => 'Saya merasa yakin untuk menggunakan konfigurasi antarmuka yang direkomendasikan oleh sistem ini.',
        ],
        'overall_satisfaction' => [
            'construct' => 'Overall Satisfaction',
            'statement' => 'Secara keseluruhan, saya puas dengan proses dan hasil rekomendasi antarmuka pada sistem ini.',
        ],
        'explanation_understandability' => [
            'construct' => 'Explanation Understandability',
            'statement' => 'Penjelasan faktor yang ditampilkan (pengaruh usia, pekerjaan, dan pola interaksi saya) mudah dipahami tanpa memerlukan pengetahuan teknis AI.',
        ],
        'causal_attribution_coherence' => [
            'construct' => 'Causal Attribution Coherence',
            'statement' => 'Saya dapat melihat dengan jelas bagaimana karakteristik profil dan kebiasaan saya memengaruhi setiap elemen tampilan yang direkomendasikan.',
        ],
        'perceived_scrutability' => [
            'construct' => 'Perceived Scrutability',
            'statement' => 'Penjelasan analisis faktor tersebut membantu saya memeriksa apakah sistem telah membaca preferensi saya secara akurat.',
        ],
        'cognitive_load_appropriateness' => [
            'construct' => 'Cognitive Load / Appropriateness',
            'statement' => 'Jumlah dan penyajian informasi pada analisis faktor tersebut ringkas dan tidak membingungkan saya.',
        ],
    ],
];
