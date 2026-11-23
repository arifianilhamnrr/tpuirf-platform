<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\AnalyticsServiceInterface;
use App\Application\Interfaces\AuthServiceInterface;
use App\Application\Interfaces\ExperimentServiceInterface;
use App\Application\Interfaces\PreferenceExperimentServiceInterface;
use App\Application\Interfaces\RecommendationServiceInterface;
use App\Application\Interfaces\UserProfileServiceInterface;
use App\Infrastructure\Persistence\Models\TrustEvaluation;
use App\Infrastructure\Persistence\Models\Recommendation;
use App\Infrastructure\Persistence\Models\XaiEvaluation;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
        private readonly AnalyticsServiceInterface $analyticsService,
        private readonly ExperimentServiceInterface $experimentService,
        private readonly PreferenceExperimentServiceInterface $preferenceExperimentService,
        private readonly RecommendationServiceInterface $recommendationService,
        private readonly UserProfileServiceInterface $userProfileService,
    ) {}

    public function respondent(): View
    {
        $user = $this->authService->getCurrentUser();
        $preferenceExperiment = $this->preferenceExperimentService->findForUser($user->id);
        $activeExperiment = $preferenceExperiment
            ? null
            : $this->experimentService->getCurrentExperiment($user->id);
        $recommendation = $this->recommendationService->getRecommendation($user->id);
        $finalPreferences = $preferenceExperiment['preferences'] ?? [];
        $trustEvaluation = TrustEvaluation::query()
            ->where('user_id', $user->id)
            ->latest()
            ->first();
        $xaiEvaluation = $recommendation?->id
            ? XaiEvaluation::query()->where('recommendation_id', $recommendation->id)->first()
            : null;
        $profileComplete = $this->userProfileService->isProfileComplete($user->id);

        return view('dashboard.respondent', compact(
            'user',
            'activeExperiment',
            'preferenceExperiment',
            'recommendation',
            'finalPreferences',
            'trustEvaluation',
            'xaiEvaluation',
            'profileComplete',
        ));
    }

    public function researcher(): View
    {
        $stats = $this->analyticsService->getDashboardStats();

        return view('dashboard.researcher', compact('stats'));
    }

    public function admin(): View
    {
        $stats = $this->analyticsService->getDashboardStats();
        $modelStatus = $this->recommendationService->getModelStatus();
        $featureImportance = array_slice($this->recommendationService->getFeatureImportance(), 0, 8);
        $recentPredictions = Recommendation::query()
            ->with('user:id,name')
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard.admin', compact('stats', 'modelStatus', 'featureImportance', 'recentPredictions'));
    }
}
