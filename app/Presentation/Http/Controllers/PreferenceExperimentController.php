<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\PreferenceExperimentServiceInterface;
use App\Application\Interfaces\RecommendationServiceInterface;
use App\Application\Interfaces\ExplainabilityServiceInterface;
use App\Application\Jobs\RetrainRecommendationModelJob;
use App\Application\Jobs\SyncExperimentToGoogleSheetsJob;
use App\Presentation\Http\Requests\InteractionEventBatchRequest;
use App\Presentation\Http\Requests\StartPreferenceExperimentRequest;
use App\Presentation\Http\Requests\TaskSelectionRequest;
use App\Presentation\Http\Requests\TrustEvaluationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PreferenceExperimentController extends Controller
{
    public function __construct(
        private readonly PreferenceExperimentServiceInterface $experimentService,
        private readonly RecommendationServiceInterface $recommendationService,
        private readonly ExplainabilityServiceInterface $explainabilityService,
    ) {}

    public function start(StartPreferenceExperimentRequest $request): JsonResponse|RedirectResponse
    {
        $experiment = $this->experimentService->start(
            $this->userId($request),
            $request->validated('consent_version'),
        );

        if ($request->expectsJson()) {
            return response()->json(['data' => $experiment]);
        }

        return match ($experiment['status']) {
            'completed' => redirect()->route(
                'respondent.preference-experiment.complete.page',
                $experiment['id'],
            ),
            'evaluation' => redirect()->route(
                'respondent.preference-experiment.evaluation.page',
                $experiment['id'],
            ),
            default => redirect()->route('respondent.preference-experiment.tasks.page', [
                'experimentId' => $experiment['id'],
                'taskKey' => $experiment['current_task'],
            ]),
        };
    }

    public function task(Request $request, string $experimentId, string $taskKey): JsonResponse
    {
        $task = $this->experimentService->getTask(
            $this->userId($request),
            $experimentId,
            $taskKey,
        );

        return response()->json(['data' => $task]);
    }

    public function select(
        TaskSelectionRequest $request,
        string $experimentId,
        string $taskKey,
    ): JsonResponse {
        $experiment = $this->experimentService->submitSelection(
            $this->userId($request),
            $experimentId,
            $taskKey,
            $request->validated(),
        );

        return response()->json(['data' => $experiment]);
    }

    public function events(
        InteractionEventBatchRequest $request,
        string $experimentId,
    ): JsonResponse {
        $result = $this->experimentService->recordEvents(
            $this->userId($request),
            $experimentId,
            $request->validated('events'),
        );

        return response()->json(['data' => $result], 202);
    }

    public function evaluate(
        TrustEvaluationRequest $request,
        string $experimentId,
    ): JsonResponse {
        $evaluation = $this->experimentService->saveEvaluation(
            $this->userId($request),
            $experimentId,
            $request->validated(),
        );

        return response()->json(['data' => $evaluation]);
    }

    public function complete(Request $request, string $experimentId): JsonResponse
    {
        $userId = $this->userId($request);
        $experiment = $this->experimentService->complete(
            $userId,
            $experimentId,
        );

        // Freeze the current respondent's prediction using the previously active model.
        $recommendation = $this->recommendationService->generateForExperiment($userId, $experimentId);

        $explanations = [];
        if ($recommendation->id) {
            try {
                $explanations = $this->explainabilityService->getExplanation($recommendation->id);
            } catch (\Throwable) {}
        }

        $recData = $recommendation->toArray();
        $recData['explanations'] = $explanations;

        // Sync the frozen prediction, then make this respondent's manual labels
        // available to the next XGBoost model version.
        SyncExperimentToGoogleSheetsJob::dispatch($userId, $experimentId, $recData)->afterCommit();
        RetrainRecommendationModelJob::dispatch($experimentId)->afterCommit();

        return response()->json([
            'data' => $experiment,
            'redirect_url' => route('respondent.recommendation.show'),
        ]);
    }

    private function userId(Request $request): string
    {
        return (string) $request->session()->get('user_id');
    }
}
