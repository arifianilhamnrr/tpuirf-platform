<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\AuthServiceInterface;
use App\Application\Interfaces\ExperimentServiceInterface;
use App\Application\Interfaces\InteractionLoggerServiceInterface;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExperimentController extends Controller
{
    public function __construct(
        private readonly ExperimentServiceInterface $experimentService,
        private readonly AuthServiceInterface $authService,
        private readonly InteractionLoggerServiceInterface $loggerService,
    ) {}

    public function start(): RedirectResponse
    {
        $user = $this->authService->getCurrentUser();
        $experiment = $this->experimentService->startExperiment($user->id);
        return redirect()->route('respondent.experiment.run', ['step' => 1]);
    }

    public function run(Request $request): View
    {
        $user = $this->authService->getCurrentUser();
        $experiment = $this->experimentService->getCurrentExperiment($user->id);

        if (!$experiment) {
            return redirect()->route('respondent.dashboard');
        }

        $step = $request->query('step', $experiment->currentStep);
        $uiTemplate = $this->experimentService->getUiForStep($experiment->id, $step);

        return view('experiment.run', [
            'experiment' => $experiment,
            'step' => (int) $step,
            'uiVersion' => $uiTemplate['version'] ?? $experiment->currentUiVersion,
            'uiConfig' => $uiTemplate['config'] ?? [],
            'totalSteps' => 4,
        ]);
    }

    public function completeStep(Request $request): RedirectResponse
    {
        $user = $this->authService->getCurrentUser();
        $experiment = $this->experimentService->getCurrentExperiment($user->id);

        if (!$experiment) {
            return redirect()->route('respondent.dashboard');
        }

        $this->experimentService->completeStep($experiment->id, $request->all());
        
        $nextStep = $experiment->currentStep + 1;
        
        if ($nextStep > 4) {
            return redirect()->route('respondent.experiment.preference');
        }

        return redirect()->route('respondent.experiment.run', ['step' => $nextStep]);
    }

    public function preference(): View
    {
        $user = $this->authService->getCurrentUser();
        $experiment = $this->experimentService->getCurrentExperiment($user->id);

        return view('experiment.preference', [
            'experiment' => $experiment,
            'uiVersions' => $experiment->uiOrder ?? ['A', 'B', 'C', 'D'],
        ]);
    }

    public function submitPreference(Request $request): RedirectResponse
    {
        $user = $this->authService->getCurrentUser();
        $experiment = $this->experimentService->getCurrentExperiment($user->id);

        $this->experimentService->selectPreferredUi($experiment->id, $request->input('preferred_ui'));

        return redirect()->route('respondent.experiment.evaluation');
    }

    public function evaluation(): View
    {
        return view('experiment.evaluation');
    }

    public function submitEvaluation(Request $request): RedirectResponse
    {
        $user = $this->authService->getCurrentUser();
        $experiment = $this->experimentService->getCurrentExperiment($user->id);

        $this->experimentService->completeExperiment($experiment->id);

        return redirect()->route('respondent.experiment.complete');
    }

    public function complete(): View
    {
        return view('experiment.complete');
    }
}
