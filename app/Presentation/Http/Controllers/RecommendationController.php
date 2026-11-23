<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\AuthServiceInterface;
use App\Application\Interfaces\ExplainabilityServiceInterface;
use App\Application\Interfaces\RecommendationServiceInterface;
use App\Application\Jobs\SyncXaiEvaluationToGoogleSheetsJob;
use App\Infrastructure\Persistence\Models\ParticipantPreference;
use App\Infrastructure\Persistence\Models\Recommendation;
use App\Infrastructure\Persistence\Models\XaiEvaluation;
use App\Presentation\Http\Requests\StoreXaiEvaluationRequest;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RecommendationController extends Controller
{
    public function __construct(
        private readonly RecommendationServiceInterface $recommendationService,
        private readonly ExplainabilityServiceInterface $explainabilityService,
        private readonly AuthServiceInterface $authService,
    ) {}

    public function show(): View
    {
        $user = $this->authService->getCurrentUser();
        $preference = ParticipantPreference::query()
            ->where('user_id', $user->id)
            ->whereHas('experiment', fn ($query) => $query->where('status', 'completed'))
            ->with('experiment')
            ->latest()
            ->first();
        $recommendation = $this->recommendationService->getRecommendation($user->id);

        if (!$recommendation) {
            $recommendation = $this->recommendationService->generateRecommendation($user->id);
        }

        $explanations = [];
        if ($recommendation->id) {
            $explanations = $this->explainabilityService->getExplanation($recommendation->id);
        }

        $xaiEvaluation = $recommendation?->id
            ? XaiEvaluation::query()
                ->where('user_id', $user->id)
                ->where('recommendation_id', $recommendation->id)
                ->first()
            : null;
        $canEvaluateXai = $recommendation
            && $recommendation->predicted_at
            && $recommendation->algorithm === 'xgboost'
            && ! str_starts_with((string) $recommendation->model_version, 'fallback-');

        return view('recommendation.show', [
            'recommendation' => $recommendation,
            'explanations' => $explanations,
            'preferenceExperiment' => $preference?->experiment,
            'finalPreferences' => $preference?->final_config ?? [],
            'xaiEvaluation' => $xaiEvaluation,
            'canEvaluateXai' => $canEvaluateXai,
            'xaiItems' => config('xai_evaluation.items'),
            'xaiScale' => config('xai_evaluation.scale'),
        ]);
    }

    public function generate(): RedirectResponse
    {
        $user = $this->authService->getCurrentUser();
        $this->recommendationService->generateRecommendation($user->id);
        return redirect()->route('respondent.recommendation.show');
    }

    public function storeXaiEvaluation(StoreXaiEvaluationRequest $request): RedirectResponse|JsonResponse
    {
        $user = $this->authService->getCurrentUser();
        $recommendation = Recommendation::query()
            ->where('user_id', $user->id)
            ->whereNotNull('experiment_id')
            ->whereNotNull('predicted_at')
            ->latest('predicted_at')
            ->firstOrFail();

        abort_if(
            $recommendation->algorithm !== 'xgboost'
                || str_starts_with((string) $recommendation->model_version, 'fallback-'),
            422,
            'Evaluasi hanya dapat dikirim setelah rekomendasi XGBoost tersedia.',
        );

        $existing = XaiEvaluation::query()->where('recommendation_id', $recommendation->id)->first();
        $attributes = $request->validated() + [
            'user_id' => $user->id,
            'experiment_id' => $recommendation->experiment_id,
            'instrument_version' => config('xai_evaluation.instrument_version'),
            'submitted_at' => $existing?->submitted_at ?? now(),
        ];
        $evaluation = XaiEvaluation::query()->updateOrCreate(
            ['recommendation_id' => $recommendation->id],
            $attributes,
        );
        SyncXaiEvaluationToGoogleSheetsJob::dispatch($evaluation->id)->afterCommit();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['data' => $evaluation->fresh()]);
        }

        return redirect()->to(route('respondent.recommendation.show').'#evaluasi-xai')
            ->with('success', 'Evaluasi rekomendasi berhasil disimpan.');
    }
}
