<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\AnalyticsServiceInterface;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsServiceInterface $analyticsService,
    ) {}

    public function index(): View
    {
        $stats = $this->analyticsService->getDashboardStats();
        $respondentGrowth = $this->analyticsService->getRespondentGrowth();
        $trustScore = $this->analyticsService->getTrustScore();
        $transparencyScore = $this->analyticsService->getTransparencyScore();
        $accuracy = $this->analyticsService->getRecommendationAccuracy();
        $clickDistribution = $this->analyticsService->getClickDistribution();
        $taskDurationStats = $this->analyticsService->getTaskDurationStats();
        $datasetGrowth = $this->analyticsService->getDatasetGrowth();
        $xaiEvaluationStats = $this->analyticsService->getXaiEvaluationStats();

        return view('analytics.index', compact(
            'stats',
            'respondentGrowth',
            'trustScore',
            'transparencyScore',
            'accuracy',
            'clickDistribution',
            'taskDurationStats',
            'datasetGrowth',
            'xaiEvaluationStats',
        ));
    }
}
