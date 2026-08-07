<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\AuthServiceInterface;
use App\Application\Interfaces\ExplainabilityServiceInterface;
use App\Application\Interfaces\RecommendationServiceInterface;
use App\Infrastructure\Persistence\Models\ParticipantPreference;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('recommendation.show', [
            'recommendation' => $recommendation,
            'explanations' => $explanations,
            'preferenceExperiment' => $preference?->experiment,
            'finalPreferences' => $preference?->final_config ?? [],
        ]);
    }

    public function generate(): RedirectResponse
    {
        $user = $this->authService->getCurrentUser();
        $this->recommendationService->generateRecommendation($user->id);
        return redirect()->route('respondent.recommendation.show');
    }
}
