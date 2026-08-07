<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\PreferenceExperimentServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class PreferenceExperimentPageController extends Controller
{
    public function __construct(
        private readonly PreferenceExperimentServiceInterface $experimentService,
    ) {}

    public function introduction(): View
    {
        return view('experiment.research-introduction', [
            'protocolVersion' => config('experiment.protocol_version'),
            'consentVersion' => config('experiment.consent_version'),
            'tasks' => config('experiment.tasks'),
        ]);
    }

    public function task(Request $request, string $experimentId, string $taskKey): View|RedirectResponse
    {
        $state = $this->experimentService->getState($this->userId($request), $experimentId);

        if ($state['status'] === 'completed') {
            return redirect()->route('respondent.preference-experiment.complete.page', $experimentId);
        }

        $requestedTask = collect($state['tasks'])->firstWhere('task_key', $taskKey);
        $canReviewCompletedTask = $requestedTask && $requestedTask['status'] === 'completed';

        if (! $canReviewCompletedTask && $state['status'] === 'evaluation') {
            return redirect()->route('respondent.preference-experiment.evaluation.page', $experimentId);
        }

        if (! $canReviewCompletedTask && $state['current_task'] !== $taskKey) {
            return redirect()->route('respondent.preference-experiment.tasks.page', [
                'experimentId' => $experimentId,
                'taskKey' => $state['current_task'],
            ]);
        }

        $task = $this->experimentService->getTask(
            $this->userId($request),
            $experimentId,
            $taskKey,
        );

        return view('experiment.research-task', [
            'experiment' => $state,
            'task' => $task,
            'taskDefinition' => config("experiment.tasks.$taskKey"),
            'idleThresholdSeconds' => config('experiment.idle_threshold_seconds'),
        ]);
    }

    public function evaluation(Request $request, string $experimentId): View|RedirectResponse
    {
        $state = $this->experimentService->getState($this->userId($request), $experimentId);

        if ($state['status'] === 'completed') {
            return redirect()->route('respondent.preference-experiment.complete.page', $experimentId);
        }

        if ($state['status'] !== 'evaluation') {
            return redirect()->route('respondent.preference-experiment.tasks.page', [
                'experimentId' => $experimentId,
                'taskKey' => $state['current_task'],
            ]);
        }

        return view('experiment.research-evaluation', ['experiment' => $state]);
    }

    public function complete(Request $request, string $experimentId): View|RedirectResponse
    {
        $state = $this->experimentService->getState($this->userId($request), $experimentId);

        if ($state['status'] !== 'completed') {
            return redirect()->route('respondent.preference-experiment.evaluation.page', $experimentId);
        }

        return view('experiment.research-complete', ['experiment' => $state]);
    }

    private function userId(Request $request): string
    {
        return (string) $request->session()->get('user_id');
    }
}
