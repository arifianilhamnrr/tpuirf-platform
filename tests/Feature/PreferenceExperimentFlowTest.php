<?php

namespace Tests\Feature;

use App\Application\Interfaces\AuthServiceInterface;
use App\Application\Interfaces\DatasetExportServiceInterface;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PreferenceExperimentFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $respondent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->respondent = User::create([
            'name' => 'Flow Participant',
            'email' => 'flow@example.test',
            'password' => 'secret-password',
            'role' => 'respondent',
            'date_of_birth' => '2001-02-03',
            'is_active' => true,
        ]);

        $this->withSession([
            'user_id' => $this->respondent->id,
            'user_role' => 'respondent',
            'user_name' => $this->respondent->name,
        ]);

        $this->respondent->profile()->create($this->completeProfileData());
    }

    public function test_incomplete_profile_cannot_open_or_start_an_experiment(): void
    {
        $this->respondent->profile()->delete();

        $this->get(route('respondent.preference-experiment.introduction'))
            ->assertRedirect(route('respondent.profile.show'))
            ->assertSessionHas('error', 'Lengkapi profil terlebih dahulu sebelum memulai eksperimen.');

        $this->post(route('respondent.preference-experiment.start'), [
            'consent_accepted' => true,
            'consent_version' => config('experiment.consent_version'),
        ])->assertRedirect(route('respondent.profile.show'));

        $this->postJson(route('respondent.preference-experiment.start'), [
            'consent_accepted' => true,
            'consent_version' => config('experiment.consent_version'),
        ])
            ->assertStatus(409)
            ->assertJsonPath('redirect_url', route('respondent.profile.show'));

        $this->assertDatabaseCount('experiments', 0);
        $this->get(route('respondent.dashboard'))
            ->assertOk()
            ->assertSee('Lengkapi Profil Anda')
            ->assertDontSee('Mulai Eksperimen');
    }

    public function test_respondent_can_open_the_consent_and_first_live_task_pages(): void
    {
        $this->get(route('respondent.preference-experiment.introduction'))
            ->assertOk()
            ->assertSee('enam aspek antarmuka')
            ->assertSee('bersedia mengikuti eksperimen ini');

        $response = $this->post(route('respondent.preference-experiment.start'), [
            'consent_accepted' => true,
            'consent_version' => config('experiment.consent_version'),
        ]);

        $experimentId = $this->respondent->experiments()->sole()->id;

        $response->assertRedirect(route('respondent.preference-experiment.tasks.page', [
            'experimentId' => $experimentId,
            'taskKey' => 'theme',
        ]));

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Tugas 1 dari 6')
            ->assertSee('Mode Terang')
            ->assertSee('Mode Gelap')
            ->assertSee('tpuirf-theme-preview', false)
            ->assertSee('applyThemePreview', false)
            ->assertSee('delta_previous_click_ms', false)
            ->assertSee('elapsed_experiment_ms', false);
    }

    public function test_final_design_task_keeps_preview_and_tracking_contracts(): void
    {
        $experiment = $this->postJson(route('respondent.preference-experiment.start'), [
            'consent_accepted' => true,
            'consent_version' => config('experiment.consent_version'),
        ])->assertOk()->json('data');

        $selections = [
            'theme' => ['selected_option' => 'light'],
            'typography' => ['selection_payload' => ['font_family' => 'sans', 'font_size' => 'medium']],
            'layout_density' => ['selected_option' => 'comfortable'],
            'navigation_position' => ['selected_option' => 'top'],
            'icon_style' => ['selected_option' => 'outline'],
        ];

        foreach ($selections as $taskKey => $selection) {
            $this->postJson(route('respondent.preference-experiment.tasks.select', [
                'experimentId' => $experiment['id'],
                'taskKey' => $taskKey,
            ]), [
                ...$selection,
                'active_duration_ms' => 100,
            ])->assertOk();
        }

        $task = $this->respondent->experiments()->findOrFail($experiment['id'])
            ->tasks()->where('task_key', 'design_style')->sole();
        $response = $this->get(route('respondent.preference-experiment.tasks.page', [
            'experimentId' => $experiment['id'],
            'taskKey' => 'design_style',
        ]))->assertOk();

        $this->assertEqualsCanonicalizing(config('experiment.tasks.design_style.options'), $task->option_order);

        foreach ($task->option_order as $style) {
            foreach (['accordion', 'live-preview', 'primary-action', 'fullscreen', 'select'] as $component) {
                $response->assertSee("data-track-component=\"design-{$style}-{$component}\"", false);
            }
        }

        foreach (['blue', 'green', 'purple', 'orange', 'rose', 'neutral'] as $palette) {
            $response->assertSee("data-track-component=\"palette-{$palette}\"", false);
        }

        $response
            ->assertSee('data-track-component="task-submit"', false)
            ->assertSee('compactSurfaceClass', false)
            ->assertSee('fullscreenSurfaceClass', false);
    }

    public function test_dashboard_resumes_the_new_preference_experiment(): void
    {
        $experiment = $this->postJson(route('respondent.preference-experiment.start'), [
            'consent_accepted' => true,
            'consent_version' => config('experiment.consent_version'),
        ])->json('data');

        $this->get(route('respondent.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Lanjutkan Penelitian Preferensi')
            ->assertSee(route('respondent.preference-experiment.tasks.page', [
                'experimentId' => $experiment['id'],
                'taskKey' => 'theme',
            ]), false)
            ->assertDontSee('Step 1 of 4');

        $this->get(route('respondent.dashboard'))
            ->assertSee('id="user-menu-button"', false)
            ->assertSee('id="user-menu-dropdown"', false)
            ->assertSee('aria-label="Buka profil"', false)
            ->assertSee('aria-label="Keluar"', false)
            ->assertSee('Gunakan mode gelap', false)
            ->assertSee('Logout');
    }

    public function test_profile_uses_birth_date_for_age_and_limits_gender_choices(): void
    {
        $this->post(route('respondent.profile.update'), [
            'gender' => 'male',
            'education' => 'bachelor',
            'occupation' => 'Peneliti',
            'digital_literacy' => 4,
            'device_type' => 'smartphone',
            'visual_preference' => 'modern',
            'context_environment' => 'home',
            'ai_familiarity' => 4,
            'daily_device_usage' => '3_5',
            'device_usage_time' => 'morning',
            'primary_device_purpose' => 'education',
        ])->assertRedirect(route('respondent.profile.show'));

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $this->respondent->id,
            'age' => $this->respondent->date_of_birth->age,
            'gender' => 'male',
            'device_type' => 'smartphone',
        ]);

        $this->get(route('respondent.profile.show'))
            ->assertOk()
            ->assertDontSee('name="age"', false)
            ->assertDontSee('name="gender"', false)
            ->assertDontSee('Nonbiner')
            ->assertDontSee('Tidak ingin menyebutkan')
            ->assertSee('Smartphone')
            ->assertSee('Edit Profil')
            ->assertSee(route('respondent.profile.edit'), false);

        $this->get(route('respondent.profile.edit'))
            ->assertOk()
            ->assertSee('name="gender"', false)
            ->assertSee('name="digital_literacy"', false)
            ->assertSee('Simpan Profil');

        $this->post(route('respondent.profile.update'), [
            'gender' => 'non-binary',
            'education' => 'bachelor',
            'digital_literacy' => 4,
            'device_type' => 'smartphone',
            'visual_preference' => 'modern',
            'context_environment' => 'home',
            'ai_familiarity' => 4,
            'daily_device_usage' => '3_5',
            'device_usage_time' => 'morning',
            'primary_device_purpose' => 'education',
        ])->assertSessionHasErrors('gender');
    }

    public function test_raw_event_export_is_pseudonymous_and_formula_safe(): void
    {
        $experiment = $this->postJson(route('respondent.preference-experiment.start'), [
            'consent_accepted' => true,
            'consent_version' => config('experiment.consent_version'),
        ])->json('data');

        $this->postJson(route('respondent.preference-experiment.events.batch', $experiment['id']), [
            'events' => [[
                'event_uuid' => (string) Str::uuid(),
                'sequence_number' => 1,
                'task_key' => 'theme',
                'component_id' => '=2+2',
                'event_type' => 'component_clicked',
            ]],
        ])->assertAccepted();

        $path = app(DatasetExportServiceInterface::class)->exportCsv('raw-events');

        try {
            $csv = file_get_contents($path);
            $this->assertStringContainsString('participant_code', $csv);
            $this->assertStringContainsString($experiment['id'], $csv);
            $this->assertStringContainsString("'=2+2", $csv);
            $this->assertStringNotContainsString($this->respondent->name, $csv);
            $this->assertStringNotContainsString('2001-02-03', $csv);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_inactive_existing_account_cannot_log_in(): void
    {
        $this->respondent->update(['is_active' => false]);
        $this->flushSession();

        $this->expectException(ValidationException::class);

        app(AuthServiceInterface::class)->login(
            $this->respondent->name,
            '2001-02-03',
        );
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
