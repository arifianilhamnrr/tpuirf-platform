<?php

namespace Tests\Feature;

use App\Application\Jobs\RetrainRecommendationModelJob;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class PreferenceExperimentBackendTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Research Participant',
            'email' => 'participant@example.test',
            'password' => 'secret-password',
            'role' => 'respondent',
            'date_of_birth' => '2000-01-01',
            'is_active' => true,
        ]);

        $this->withSession([
            'user_id' => $this->user->id,
            'user_role' => 'respondent',
            'user_name' => $this->user->name,
        ]);

        $this->user->profile()->create($this->completeProfileData());
    }

    public function test_it_starts_one_resumable_experiment_with_six_tasks(): void
    {
        $first = $this->startExperiment();
        $second = $this->startExperiment();

        $this->assertSame($first['id'], $second['id']);
        $this->assertSame('theme', $first['current_task']);
        $this->assertCount(6, $first['tasks']);
        $this->assertDatabaseCount('experiments', 1);
        $this->assertDatabaseCount('experiment_tasks', 6);
        $this->assertDatabaseCount('participant_preferences', 1);
        $this->assertSame('design_style', $first['tasks'][5]['task_key']);

        $this->getJson(route('respondent.preference-experiment.tasks.show', [
            'experimentId' => $first['id'],
            'taskKey' => 'theme',
        ]))
            ->assertOk()
            ->assertJsonCount(2, 'data.option_order')
            ->assertJsonPath('data.status', 'in_progress');
    }

    public function test_admin_dashboard_monitors_integrated_models_and_shap_outputs(): void
    {
        Http::fake([
            '*/health' => Http::response([
                'status' => 'healthy',
                'model_loaded' => true,
                'models' => ['xgboost'],
                'model_version' => 'xgb-v4-test-snapshot',
                'accuracy' => 0.88,
                'targets' => ['theme', 'font_family', 'design_style'],
            ]),
            '*/feature_importance' => Http::response([
                ['feature' => 'theme', 'importance' => 0.31],
                ['feature' => 'font_family', 'importance' => 0.22],
            ]),
        ]);
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.test',
            'password' => 'secret-password',
            'role' => 'admin',
            'date_of_birth' => '1990-01-01',
            'is_active' => true,
        ]);

        $this->withSession([
            'user_id' => $admin->id,
            'user_role' => 'admin',
            'user_name' => $admin->name,
        ])->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('XGBoost + SHAP')
            ->assertSee('Model aktif')
            ->assertSee('Theme');
    }

    public function test_it_persists_all_preferences_evaluation_and_completion(): void
    {
        Queue::fake();
        Http::fake([
            '*/train' => Http::response(['status' => 'trained', 'accuracy' => 0.85]),
            '*/predict' => Http::response([
                'recommended_ui' => 'minimal_flat',
                'recommended_config' => [
                    'theme' => 'dark',
                    'color_palette' => 'blue',
                    'font_family' => 'high_legibility',
                    'font_size' => 'large',
                    'layout_density' => 'comfortable',
                    'navigation_position' => 'bottom',
                    'icon_style' => 'filled',
                    'design_style' => 'minimal_flat',
                ],
                'confidence_score' => 0.87,
                'confidence_by_target' => ['theme' => 0.91, 'font_family' => 0.84, 'design_style' => 0.86],
                'shap_values' => [0.42, -0.18],
                'features' => ['theme', 'font_family'],
                'explanations' => [
                    ['target' => 'theme', 'recommended_value' => 'dark', 'feature' => 'device_usage_time', 'feature_value' => 'evening', 'shap_value' => 0.42, 'impact' => 0.42, 'direction' => 'increases'],
                    ['target' => 'font_family', 'recommended_value' => 'high_legibility', 'feature' => 'device_type', 'feature_value' => 'smartphone', 'shap_value' => -0.18, 'impact' => 0.18, 'direction' => 'decreases'],
                ],
                'model_outputs' => [
                    'theme' => [
                        'algorithm' => 'xgboost',
                        'prediction' => 'dark',
                        'confidence' => 0.92,
                    ],
                ],
                'model_version' => 'xgb-v4-test-snapshot',
            ]),
        ]);
        $experiment = $this->startExperiment();
        $experimentId = $experiment['id'];
        $selections = [
            'theme' => ['selected_option' => 'dark'],
            'typography' => [
                'selection_payload' => [
                    'font_family' => 'high_legibility',
                    'font_size' => 'large',
                ],
            ],
            'layout_density' => ['selected_option' => 'comfortable'],
            'navigation_position' => ['selected_option' => 'bottom'],
            'icon_style' => ['selected_option' => 'outline'],
            'design_style' => [
                'selection_payload' => [
                    'design_styles' => ['glassmorphism', 'minimal_flat'],
                    'color_palette' => 'blue',
                ],
            ],
        ];

        foreach ($selections as $taskKey => $selection) {
            $this->getJson(route('respondent.preference-experiment.tasks.show', [
                'experimentId' => $experimentId,
                'taskKey' => $taskKey,
            ]))->assertOk();

            $response = $this->postJson(route('respondent.preference-experiment.tasks.select', [
                'experimentId' => $experimentId,
                'taskKey' => $taskKey,
            ]), [
                ...$selection,
                'active_duration_ms' => 1500,
                'idle_duration_ms' => 100,
                'change_count' => 1,
            ])->assertOk();

            $experiment = $response->json('data');
        }

        $this->assertSame('evaluation', $experiment['status']);
        $this->assertNull($experiment['current_task']);
        $this->assertDatabaseHas('participant_preferences', [
            'experiment_id' => $experimentId,
            'user_id' => $this->user->id,
            'theme' => 'dark',
            'color_palette' => 'blue',
            'font_family' => 'high_legibility',
            'font_size' => 'large',
            'layout_density' => 'comfortable',
            'navigation_position' => 'bottom',
            'icon_style' => 'outline',
            'design_style' => 'glassmorphism|minimal_flat',
        ]);
        $this->assertSame(
            ['glassmorphism', 'minimal_flat'],
            $this->user->experiments()->findOrFail($experimentId)
                ->participantPreference->final_config['design_styles'],
        );
        $this->assertSame(
            'blue',
            $this->user->experiments()->findOrFail($experimentId)
                ->participantPreference->final_config['color_palette'],
        );

        $this->postJson(route('respondent.preference-experiment.complete', [
            'experimentId' => $experimentId,
        ]))->assertUnprocessable();

        $this->postJson(route('respondent.preference-experiment.evaluation', [
            'experimentId' => $experimentId,
        ]), [
            'trust_score' => 4,
            'transparency_score' => 5,
            'ui_satisfaction' => 4,
            'recommendation_quality' => 3,
            'ai_usage_frequency' => 'often',
            'ai_answer_trust' => 4,
            'ai_explanation_preference' => 5,
            'feedback' => 'The live previews were clear.',
        ])->assertOk();

        $this->postJson(route('respondent.preference-experiment.complete', [
            'experimentId' => $experimentId,
        ]))
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/predict'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/train'));
        Queue::assertPushed(RetrainRecommendationModelJob::class);

        $predictRequest = collect(Http::recorded())
            ->first(fn (array $recorded) => str_contains($recorded[0]->url(), '/predict'))[0];
        foreach (['theme', 'color_palette', 'font_family', 'font_size', 'layout_density', 'navigation_position', 'icon_style', 'design_style'] as $target) {
            $this->assertArrayNotHasKey($target, $predictRequest->data());
        }

        $this->assertDatabaseHas('trust_evaluations', [
            'experiment_id' => $experimentId,
            'user_id' => $this->user->id,
            'trust_score' => 4,
            'transparency_score' => 5,
            'ui_satisfaction' => 4,
            'ai_usage_frequency' => 'often',
            'ai_answer_trust' => 4,
            'ai_explanation_preference' => 5,
        ]);
        $this->assertDatabaseCount('interaction_events', 9);
        $this->assertDatabaseHas('interaction_events', [
            'experiment_id' => $experimentId,
            'event_type' => 'evaluation_submitted',
        ]);
        $this->assertDatabaseHas('recommendations', [
            'experiment_id' => $experimentId,
            'user_id' => $this->user->id,
            'recommended_ui' => 'minimal_flat',
            'model_version' => 'xgb-v4-test-snapshot',
        ]);
        $recommendation = $this->user->recommendations()->where('experiment_id', $experimentId)->firstOrFail();
        $this->assertSame('device_usage_time', $recommendation->explanations[0]['feature']);
        foreach (['theme', 'color_palette', 'font_family', 'font_size', 'layout_density', 'navigation_position', 'icon_style', 'design_style'] as $target) {
            $this->assertArrayNotHasKey($target, $recommendation->input_features);
        }
        $this->assertSame('evening', $recommendation->input_features['device_usage_time']);
        $this->assertSame('high_legibility', $recommendation->recommended_config['font_family']);
        $this->assertSame(0.91, $recommendation->confidence_by_target['theme']);
        $this->assertSame('xgboost', $recommendation->model_outputs['theme']['algorithm']);

        $this->get(route('respondent.recommendation.show'))
            ->assertOk()
            ->assertSee('Konfigurasi Pilihan Sistem (AI)')
            ->assertSee('Waktu Penggunaan Utama')
            ->assertSee('Tema')
            ->assertSee('sore');
        $this->assertDatabaseHas('interaction_events', [
            'experiment_id' => $experimentId,
            'event_type' => 'experiment_completed',
        ]);
    }

    public function test_event_batches_are_owned_ordered_and_idempotent(): void
    {
        $experiment = $this->startExperiment();
        $experimentId = $experiment['id'];
        $eventUuidA = (string) Str::uuid();
        $eventUuidB = (string) Str::uuid();
        $events = [
            [
                'event_uuid' => $eventUuidA,
                'sequence_number' => 1,
                'task_key' => 'theme',
                'option_id' => 'dark',
                'component_id' => 'theme-dark-card',
                'event_type' => 'component_clicked',
                'elapsed_task_ms' => 800,
                'elapsed_experiment_ms' => 800,
                'delta_previous_event_ms' => 800,
                'delta_previous_click_ms' => 800,
                'viewport_width' => 390,
                'viewport_height' => 844,
                'input_method' => 'touch',
            ],
            [
                'event_uuid' => $eventUuidB,
                'sequence_number' => 2,
                'task_key' => 'theme',
                'option_id' => 'light',
                'component_id' => 'theme-light-card',
                'previous_component_id' => 'theme-dark-card',
                'event_type' => 'component_clicked',
                'elapsed_task_ms' => 2100,
                'elapsed_experiment_ms' => 2100,
                'delta_previous_event_ms' => 1300,
                'delta_previous_click_ms' => 1300,
                'viewport_width' => 390,
                'viewport_height' => 844,
                'input_method' => 'touch',
            ],
        ];

        $route = route('respondent.preference-experiment.events.batch', [
            'experimentId' => $experimentId,
        ]);

        $this->postJson($route, ['events' => $events])
            ->assertStatus(202)
            ->assertJsonPath('data.inserted', 2)
            ->assertJsonPath('data.duplicates', 0);

        $this->postJson($route, ['events' => $events])
            ->assertStatus(202)
            ->assertJsonPath('data.inserted', 0)
            ->assertJsonPath('data.duplicates', 2);

        $this->assertDatabaseCount('interaction_events', 3);
        $this->assertDatabaseHas('interaction_events', [
            'experiment_id' => $experimentId,
            'sequence_number' => 0,
            'event_type' => 'experiment_started',
        ]);
        $this->assertDatabaseHas('interaction_events', [
            'experiment_id' => $experimentId,
            'user_id' => $this->user->id,
            'sequence_number' => 2,
            'previous_component_id' => 'theme-dark-card',
            'delta_previous_click_ms' => 1300,
        ]);
    }

    public function test_stale_client_sequences_are_rebased_without_losing_events(): void
    {
        $experiment = $this->startExperiment();
        $experimentId = $experiment['id'];

        $this->postJson(route('respondent.preference-experiment.tasks.select', [
            'experimentId' => $experimentId,
            'taskKey' => 'theme',
        ]), [
            'selected_option' => 'dark',
            'active_duration_ms' => 100,
        ])->assertOk();

        $eventUuid = (string) Str::uuid();
        $route = route('respondent.preference-experiment.events.batch', $experimentId);
        $event = [
            'event_uuid' => $eventUuid,
            'sequence_number' => 1,
            'task_key' => 'typography',
            'event_type' => 'task_viewed',
        ];

        $this->postJson($route, ['events' => [$event]])
            ->assertAccepted()
            ->assertJsonPath('data.inserted', 1)
            ->assertJsonPath('data.last_sequence_number', 2);

        $this->assertDatabaseHas('interaction_events', [
            'experiment_id' => $experimentId,
            'event_uuid' => $eventUuid,
            'sequence_number' => 2,
        ]);

        $this->postJson($route, ['events' => [$event]])
            ->assertAccepted()
            ->assertJsonPath('data.inserted', 0)
            ->assertJsonPath('data.duplicates', 1);
    }

    public function test_latest_unsubmitted_selection_is_restored_as_a_draft(): void
    {
        $experiment = $this->startExperiment();
        $experimentId = $experiment['id'];

        $this->postJson(route('respondent.preference-experiment.tasks.select', [
            'experimentId' => $experimentId,
            'taskKey' => 'theme',
        ]), [
            'selected_option' => 'dark',
            'active_duration_ms' => 100,
        ])->assertOk();

        $this->postJson(route('respondent.preference-experiment.events.batch', $experimentId), [
            'events' => [[
                'event_uuid' => (string) Str::uuid(),
                'sequence_number' => 1,
                'task_key' => 'typography',
                'option_id' => 'large',
                'event_type' => 'selection_changed',
                'payload' => [
                    'font_family' => 'serif',
                    'font_size' => 'large',
                ],
            ]],
        ])->assertAccepted();

        $this->getJson(route('respondent.preference-experiment.tasks.show', [
            'experimentId' => $experimentId,
            'taskKey' => 'typography',
        ]))
            ->assertOk()
            ->assertJsonPath('data.draft_option', 'large')
            ->assertJsonPath('data.draft_selection_payload.font_family', 'serif')
            ->assertJsonPath('data.draft_selection_payload.font_size', 'large');
    }

    public function test_completed_preferences_can_be_reviewed_and_revised(): void
    {
        $experiment = $this->startExperiment();
        $experimentId = $experiment['id'];

        $this->postJson(route('respondent.preference-experiment.tasks.select', [
            'experimentId' => $experimentId,
            'taskKey' => 'theme',
        ]), [
            'selected_option' => 'dark',
            'active_duration_ms' => 100,
        ])->assertOk()->assertJsonPath('data.current_task', 'typography');

        $this->get(route('respondent.preference-experiment.tasks.page', [
            'experimentId' => $experimentId,
            'taskKey' => 'theme',
        ]))
            ->assertOk()
            ->assertSee('Simpan Perubahan')
            ->assertSee('initialSelectedOption', false);

        $this->postJson(route('respondent.preference-experiment.tasks.select', [
            'experimentId' => $experimentId,
            'taskKey' => 'theme',
        ]), [
            'selected_option' => 'light',
            'active_duration_ms' => 50,
            'change_count' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('data.current_task', 'typography');

        $this->assertDatabaseHas('participant_preferences', [
            'experiment_id' => $experimentId,
            'theme' => 'light',
        ]);
        $this->assertDatabaseHas('interaction_events', [
            'experiment_id' => $experimentId,
            'task_key' => 'theme',
            'event_type' => 'task_revised',
        ]);
    }

    public function test_it_rejects_invalid_or_out_of_order_selections(): void
    {
        $experiment = $this->startExperiment();

        $this->postJson(route('respondent.preference-experiment.tasks.select', [
            'experimentId' => $experiment['id'],
            'taskKey' => 'theme',
        ]), [
            'selected_option' => 'sepia',
            'active_duration_ms' => 100,
        ])->assertUnprocessable();

        $this->postJson(route('respondent.preference-experiment.tasks.select', [
            'experimentId' => $experiment['id'],
            'taskKey' => 'design_style',
        ]), [
            'selected_option' => 'glassmorphism',
            'active_duration_ms' => 100,
        ])->assertUnprocessable();
    }

    public function test_design_style_requires_one_or_more_unique_valid_options(): void
    {
        $experiment = $this->startExperiment();
        $experimentId = $experiment['id'];

        foreach ([
            'theme' => ['selected_option' => 'dark'],
            'typography' => ['selection_payload' => ['font_family' => 'serif', 'font_size' => 'large']],
            'layout_density' => ['selected_option' => 'spacious'],
            'navigation_position' => ['selected_option' => 'left'],
            'icon_style' => ['selected_option' => 'duotone'],
        ] as $taskKey => $selection) {
            $this->postJson(route('respondent.preference-experiment.tasks.select', [$experimentId, $taskKey]), [
                ...$selection,
                'active_duration_ms' => 100,
            ])->assertOk();
        }

        $route = route('respondent.preference-experiment.tasks.select', [$experimentId, 'design_style']);

        $this->postJson($route, [
            'selection_payload' => ['design_styles' => []],
            'active_duration_ms' => 100,
        ])->assertUnprocessable();

        $this->postJson($route, [
            'selection_payload' => ['design_styles' => ['glassmorphism', 'glassmorphism']],
            'active_duration_ms' => 100,
        ])->assertUnprocessable();

        $this->postJson($route, [
            'selection_payload' => ['design_styles' => ['glassmorphism', 'unknown_style']],
            'active_duration_ms' => 100,
        ])->assertUnprocessable();
    }

    public function test_a_respondent_cannot_access_another_participants_experiment(): void
    {
        $experiment = $this->startExperiment();
        $otherUser = User::create([
            'name' => 'Other Participant',
            'email' => 'other@example.test',
            'password' => 'secret-password',
            'role' => 'respondent',
            'date_of_birth' => '1999-01-01',
            'is_active' => true,
        ]);
        $otherUser->profile()->create($this->completeProfileData());

        $this->withSession([
            'user_id' => $otherUser->id,
            'user_role' => 'respondent',
            'user_name' => $otherUser->name,
        ]);

        $this->getJson(route('respondent.preference-experiment.tasks.show', [
            'experimentId' => $experiment['id'],
            'taskKey' => 'theme',
        ]))->assertNotFound();

        $this->postJson(route('respondent.preference-experiment.events.batch', [
            'experimentId' => $experiment['id'],
        ]), [
            'events' => [[
                'event_uuid' => (string) Str::uuid(),
                'sequence_number' => 1,
                'task_key' => 'theme',
                'event_type' => 'task_viewed',
            ]],
        ])->assertNotFound();
    }

    private function startExperiment(): array
    {
        return $this->postJson(route('respondent.preference-experiment.start'), [
            'consent_accepted' => true,
            'consent_version' => config('experiment.consent_version'),
        ])
            ->assertOk()
            ->json('data');
    }

    private function completeProfileData(): array
    {
        return [
            'age' => 25,
            'gender' => 'male',
            'education' => 'bachelor',
            'occupation' => 'Mahasiswa',
            'digital_literacy' => 4,
            'device_type' => 'smartphone',
            'visual_preference' => 'modern',
            'context_environment' => 'school',
            'ai_familiarity' => 3,
            'daily_device_usage' => '3_5',
            'device_usage_time' => 'evening',
            'primary_device_purpose' => 'productivity',
        ];
    }
}
