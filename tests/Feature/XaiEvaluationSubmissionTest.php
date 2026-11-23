<?php

namespace Tests\Feature;

use App\Application\Jobs\RetrainRecommendationModelJob;
use App\Application\Jobs\SyncXaiEvaluationToGoogleSheetsJob;
use App\Infrastructure\Persistence\Models\Experiment;
use App\Infrastructure\Persistence\Models\Recommendation;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class XaiEvaluationSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_respondent_can_submit_once_without_changing_frozen_prediction_or_triggering_training(): void
    {
        Queue::fake();
        [$user, $experiment, $recommendation] = $this->fixture();
        $before = $recommendation->only(['recommended_config', 'shap_values', 'model_version', 'input_features']);

        $response = $this->withSession([
            'user_id' => $user->id,
            'user_role' => 'respondent',
            'user_name' => $user->name,
        ])->post(route('respondent.recommendation.xai-evaluation.store'), $this->validPayload());

        $response->assertRedirect(route('respondent.recommendation.show').'#evaluasi-xai');
        $this->assertDatabaseHas('xai_evaluations', [
            'recommendation_id' => $recommendation->id,
            'experiment_id' => $experiment->id,
            'perceived_transparency' => 5,
            'instrument_version' => 'XAI-LIKERT-v1',
        ]);
        $this->assertSame($before, $recommendation->fresh()->only(array_keys($before)));
        Queue::assertPushed(SyncXaiEvaluationToGoogleSheetsJob::class);
        Queue::assertNotPushed(RetrainRecommendationModelJob::class);
    }

    public function test_all_items_are_required_integers_between_one_and_five(): void
    {
        [$user] = $this->fixture();
        $session = ['user_id' => $user->id, 'user_role' => 'respondent', 'user_name' => $user->name];

        $this->withSession($session)->postJson(route('respondent.recommendation.xai-evaluation.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(array_keys(config('xai_evaluation.items')));

        $invalid = array_fill_keys(array_keys(config('xai_evaluation.items')), 6);
        $this->withSession($session)->postJson(route('respondent.recommendation.xai-evaluation.store'), $invalid)
            ->assertUnprocessable();
    }

    public function test_respondent_cannot_submit_evaluation_for_another_respondents_recommendation(): void
    {
        [$owner] = $this->fixture();
        $intruder = $this->createRespondent('intruder');
        $intruder->profile()->create($this->completeProfileData());

        $this->withSession([
            'user_id' => $intruder->id,
            'user_role' => 'respondent',
            'user_name' => $intruder->name,
        ])->postJson(route('respondent.recommendation.xai-evaluation.store'), $this->validPayload())
            ->assertNotFound();

        $this->assertDatabaseMissing('xai_evaluations', ['user_id' => $owner->id]);
    }

    public function test_legacy_non_xgboost_recommendation_cannot_be_evaluated(): void
    {
        [$user, , $recommendation] = $this->fixture();
        $recommendation->update([
            'algorithm' => 'random_forest+xgboost',
            'model_version' => 'rf+xgboost+shap-multitarget-v3',
        ]);
        $session = ['user_id' => $user->id, 'user_role' => 'respondent', 'user_name' => $user->name];

        $this->withSession($session)
            ->postJson(route('respondent.recommendation.xai-evaluation.store'), $this->validPayload())
            ->assertUnprocessable();

        $this->assertDatabaseCount('xai_evaluations', 0);
    }

    public function test_resubmission_updates_same_row(): void
    {
        [$user] = $this->fixture();
        $session = ['user_id' => $user->id, 'user_role' => 'respondent', 'user_name' => $user->name];
        $this->withSession($session)->post(route('respondent.recommendation.xai-evaluation.store'), $this->validPayload());
        $updated = array_fill_keys(array_keys(config('xai_evaluation.items')), 2);
        $this->withSession($session)->post(route('respondent.recommendation.xai-evaluation.store'), $updated);

        $this->assertDatabaseCount('xai_evaluations', 1);
        $this->assertDatabaseHas('xai_evaluations', ['system_trust' => 2]);
    }

    private function fixture(): array
    {
        $user = $this->createRespondent('owner');
        $experiment = Experiment::create([
            'user_id' => $user->id,
            'status' => 'completed',
            'protocol_version' => config('experiment.protocol_version'),
            'completed_at' => now(),
        ]);
        $recommendation = Recommendation::create([
            'user_id' => $user->id,
            'experiment_id' => $experiment->id,
            'recommended_ui' => 'minimal_flat',
            'recommended_config' => ['theme' => 'dark'],
            'confidence_score' => .8,
            'shap_values' => [.5],
            'features' => ['age'],
            'explanations' => [['feature' => 'age']],
            'input_features' => ['age' => 25],
            'model_version' => 'xgb-v4-test',
            'algorithm' => 'xgboost',
            'predicted_at' => now(),
        ]);
        $user->profile()->create($this->completeProfileData());

        return [$user, $experiment, $recommendation];
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

    private function createRespondent(string $suffix): User
    {
        return User::create([
            'name' => 'XAI Respondent '.ucfirst($suffix),
            'email' => "xai-{$suffix}@example.test",
            'password' => 'secret-password',
            'role' => 'respondent',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    private function validPayload(): array
    {
        return [
            'perceived_transparency' => 5,
            'system_trust' => 4,
            'decision_confidence' => 4,
            'overall_satisfaction' => 5,
            'explanation_understandability' => 4,
            'causal_attribution_coherence' => 4,
            'perceived_scrutability' => 4,
            'cognitive_load_appropriateness' => 5,
        ];
    }
}
