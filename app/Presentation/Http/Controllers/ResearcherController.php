<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\AnalyticsServiceInterface;
use App\Application\Interfaces\DatasetExportServiceInterface;
use App\Domain\Repositories\ExperimentRepositoryInterface;
use App\Domain\Repositories\InteractionLogRepositoryInterface;
use App\Domain\Repositories\RecommendationRepositoryInterface;
use App\Domain\Repositories\UiTemplateRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ResearcherController extends Controller
{
    public function __construct(
        private readonly ExperimentRepositoryInterface $experimentRepository,
        private readonly InteractionLogRepositoryInterface $logRepository,
        private readonly RecommendationRepositoryInterface $recommendationRepository,
        private readonly UiTemplateRepositoryInterface $uiTemplateRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly AnalyticsServiceInterface $analyticsService,
        private readonly DatasetExportServiceInterface $exportService,
    ) {}

    public function experiments(): View
    {
        $experiments = $this->experimentRepository->findAll();
        return view('dashboard.researcher', compact('experiments'));
    }

    public function templates(): View
    {
        $templates = $this->uiTemplateRepository->findAll();
        return view('dashboard.researcher', compact('templates'));
    }

    public function createTemplate(): View
    {
        return view('dashboard.researcher');
    }

    public function dataset(): View
    {
        $dataset = $this->exportService->getFullDataset();
        return view('dashboard.researcher', ['dataset' => $dataset]);
    }

    public function statistics(): View
    {
        $stats = $this->analyticsService->getDashboardStats();
        return view('dashboard.researcher', compact('stats'));
    }

    public function interactionLogs(): View
    {
        $logs = $this->logRepository->findAll();
        return view('dashboard.researcher', compact('logs'));
    }

    public function viewModel(): View
    {
        $accuracy = $this->analyticsService->getRecommendationAccuracy();
        return view('dashboard.researcher', compact('accuracy'));
    }
}
