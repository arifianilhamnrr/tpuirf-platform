<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\PreferenceExperimentServiceInterface;
use App\Application\Interfaces\RecommendationServiceInterface;
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
        $experiment = $this->experimentService->complete(
            $this->userId($request),
            $experimentId,
        );
        $this->recommendationService->generateForExperiment($this->userId($request), $experimentId);

        return response()->json(['data' => $experiment]);
    }

    private function userId(Request $request): string
    {
        return (string) $request->session()->get('user_id');
    }
}
