<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\AnalyticsServiceInterface;
use App\Application\Interfaces\AuthServiceInterface;
use App\Application\Interfaces\ExperimentServiceInterface;
use App\Application\Interfaces\PreferenceExperimentServiceInterface;
use App\Application\Interfaces\RecommendationServiceInterface;
use App\Application\Interfaces\UserProfileServiceInterface;
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
        $profileComplete = $this->userProfileService->isProfileComplete($user->id);

        return view('dashboard.respondent', compact(
            'user',
            'activeExperiment',
            'preferenceExperiment',
            'recommendation',
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

        return view('dashboard.admin', compact('stats'));
    }
}
