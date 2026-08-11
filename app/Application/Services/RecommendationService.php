<?php

namespace App\Application\Services;

use App\Application\Interfaces\RecommendationServiceInterface;
use App\Application\Interfaces\DatasetExportServiceInterface;
use App\Domain\DTOs\RecommendationDTO;
use App\Domain\Repositories\RecommendationRepositoryInterface;
use App\Domain\Repositories\UserProfileRepositoryInterface;
use App\Infrastructure\Persistence\Models\Experiment;
use App\Infrastructure\Persistence\Models\Recommendation;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RecommendationService implements RecommendationServiceInterface
{
    public function __construct(
        private readonly RecommendationRepositoryInterface $recommendationRepository,
        private readonly UserProfileRepositoryInterface $profileRepository,
        private readonly DatasetExportServiceInterface $datasetExportService,
    ) {}

    public function generateRecommendation(string $userId): RecommendationDTO
    {
        $experiment = Experiment::query()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();

        return $experiment
            ? $this->generateForExperiment($userId, $experiment->id)
            : $this->persistResult($userId, null, $this->fallbackResult([]), []);
    }

    public function generateForExperiment(string $userId, string $experimentId): RecommendationDTO
    {
        $experiment = Experiment::query()
            ->whereKey($experimentId)
            ->where('user_id', $userId)
            ->with(['participantPreference', 'tasks', 'interactionEvents'])
            ->firstOrFail();
        $input = $this->modelInput($userId, $experiment);

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->post(config('app.ml_service_url', 'http://localhost:5000').'/predict', $input);

            if (! $response->successful()) {
                throw new RuntimeException($response->json('error') ?? 'ML service error');
            }

            $result = $response->json();
            if (! isset($result['recommended_ui'], $result['confidence_score'])) {
                throw new RuntimeException('Respons ML tidak lengkap.');
            }
        } catch (\Throwable $exception) {
            report($exception);
            $result = $this->fallbackResult($input);
        }

        return $this->persistResult($userId, $experimentId, $result, $input);
    }

    public function getRecommendation(string $userId): ?RecommendationDTO
    {
        $recommendations = $this->recommendationRepository->findByUserId($userId);

        return $recommendations[0] ?? null;
    }

    public function trainModel(): bool
    {
        try {
            $dataPath = $this->datasetExportService->exportCsv('full');

            return Http::timeout(300)
                ->post(config('app.ml_service_url', 'http://localhost:5000').'/train', ['data_path' => $dataPath])
                ->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    public function getModelAccuracy(): float
    {
        return (float) ($this->modelRequest('/accuracy')['accuracy'] ?? 0);
    }

    public function getModelStatus(): array
    {
        return $this->modelRequest('/health') + [
            'status' => 'unavailable',
            'model_loaded' => false,
            'models' => [],
            'accuracy' => null,
        ];
    }

    public function getFeatureImportance(): array
    {
        $result = $this->modelRequest('/feature_importance');

        return array_is_list($result) ? $result : [];
    }

    private function modelInput(string $userId, Experiment $experiment): array
    {
        $profile = $this->profileRepository->findByUserId($userId);
        $preferences = $experiment->participantPreference?->final_config ?? [];
        $events = $experiment->interactionEvents;
        $tasks = $experiment->tasks;

        return [
            'user_id' => $userId,
            'experiment_id' => $experiment->id,
            'age' => $profile?->age,
            'gender' => $profile?->gender,
            'education' => $profile?->education,
            'occupation' => $profile?->occupation,
            'digital_literacy' => $profile?->digitalLiteracy,
            'device_type' => $profile?->deviceType,
            'visual_preference' => $profile?->visualPreference,
            'context_environment' => $profile?->contextEnvironment,
            'ai_familiarity' => $profile?->aiFamiliarity,
            'click_count' => $events->where('event_type', 'component_clicked')->count(),
            'scroll_count' => $events->where('event_type', 'scroll_ended')->count(),
            'hover_count' => $events->where('event_type', 'component_hovered')->count(),
            'keyboard_count' => $events->where('input_method', 'keyboard')->count(),
            'avg_task_time' => (float) ($tasks->avg('active_duration_ms') ?? 0),
            'total_session_time' => (int) $experiment->total_active_duration_ms,
            'theme' => $preferences['theme'] ?? null,
            'color_palette' => $preferences['color_palette'] ?? null,
            'font_family' => $preferences['font_family'] ?? null,
            'font_size' => $preferences['font_size'] ?? null,
            'layout_density' => $preferences['layout_density'] ?? null,
            'navigation_position' => $preferences['navigation_position'] ?? null,
            'icon_style' => $preferences['icon_style'] ?? null,
            'design_style' => implode('|', $preferences['design_styles'] ?? []),
        ];
    }

    private function persistResult(string $userId, ?string $experimentId, array $result, array $input): RecommendationDTO
    {
        $attributes = [
            'user_id' => $userId,
            'experiment_id' => $experimentId,
            'recommended_ui' => (string) $result['recommended_ui'],
            'confidence_score' => (float) $result['confidence_score'],
            'shap_values' => $result['shap_values'] ?? null,
            'features' => $result['features'] ?? null,
            'explanations' => $result['explanations'] ?? null,
            'model_outputs' => $result['model_outputs'] ?? null,
            'input_features' => $input,
            'model_version' => $result['model_version'] ?? 'unknown',
        ];

        $recommendation = $experimentId
            ? Recommendation::query()->updateOrCreate(['experiment_id' => $experimentId], $attributes)
            : Recommendation::query()->create($attributes);

        return RecommendationDTO::fromArray($recommendation->fresh()->toArray());
    }

    private function fallbackResult(array $input): array
    {
        return [
            'recommended_ui' => explode('|', (string) ($input['design_style'] ?? ''))[0] ?: match ($input['visual_preference'] ?? null) {
                'minimal' => 'minimal_flat', 'vibrant' => 'high_contrast_brutalist', 'classic' => 'material_elevated', default => 'minimal_flat',
            },
            'confidence_score' => 0,
            'shap_values' => [],
            'features' => [],
            'explanations' => [],
            'model_outputs' => ['status' => 'ml_service_unavailable'],
            'model_version' => 'fallback-no-shap',
        ];
    }

    private function modelRequest(string $path): array
    {
        try {
            $response = Http::timeout(10)->get(config('app.ml_service_url', 'http://localhost:5000').$path);

            return $response->successful() ? $response->json() : [];
        } catch (\Throwable) {
            return [];
        }
    }
}
