<?php

namespace App\Application\Services;

use App\Application\Interfaces\AnalyticsServiceInterface;
use App\Domain\Repositories\ExperimentRepositoryInterface;
use App\Domain\Repositories\InteractionLogRepositoryInterface;
use App\Domain\Repositories\RecommendationRepositoryInterface;
use App\Domain\Repositories\TrustEvaluationRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Models\ExperimentTask;
use App\Infrastructure\Persistence\Models\InteractionEvent;

class AnalyticsService implements AnalyticsServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly ExperimentRepositoryInterface $experimentRepository,
        private readonly InteractionLogRepositoryInterface $logRepository,
        private readonly RecommendationRepositoryInterface $recommendationRepository,
        private readonly TrustEvaluationRepositoryInterface $trustRepository,
    ) {}

    public function getDashboardStats(): array
    {
        return [
            'total_respondents' => $this->userRepository->count(['role' => 'respondent']),
            'total_experiments' => $this->experimentRepository->count(),
            'completed_experiments' => $this->experimentRepository->count(['status' => 'completed']),
            'total_interactions' => $this->logRepository->count() + InteractionEvent::count(),
            'total_recommendations' => $this->recommendationRepository->count(),
            'avg_trust_score' => $this->trustRepository->getAverageTrustScore(),
            'avg_transparency_score' => $this->trustRepository->getAverageTransparencyScore(),
        ];
    }

    public function getRespondentGrowth(): array
    {
        $users = $this->userRepository->findAll(['role' => 'respondent']);
        $growth = [];

        foreach ($users as $user) {
            $date = $user->createdAt ? substr($user->createdAt, 0, 10) : 'unknown';
            $growth[$date] = ($growth[$date] ?? 0) + 1;
        }

        return $growth;
    }

    public function getInteractionHeatmap(?string $experimentId = null): array
    {
        return InteractionEvent::query()
            ->when($experimentId, fn ($query) => $query->where('experiment_id', $experimentId))
            ->where('event_type', 'component_clicked')
            ->whereNotNull('component_id')
            ->selectRaw('task_key, component_id, COUNT(*) as event_count')
            ->groupBy('task_key', 'component_id')
            ->orderByDesc('event_count')
            ->get()
            ->toArray();
    }

    public function getClickDistribution(?string $experimentId = null): array
    {
        return $this->getInteractionHeatmap($experimentId);
    }

    public function getTaskDurationStats(): array
    {
        return ExperimentTask::query()
            ->where('status', 'completed')
            ->selectRaw('task_key, COUNT(*) as sample_count, AVG(active_duration_ms) as avg_active_duration_ms, AVG(idle_duration_ms) as avg_idle_duration_ms')
            ->groupBy('task_key')
            ->orderByRaw('MIN(sequence)')
            ->get()
            ->toArray();
    }

    public function getRecommendationAccuracy(): float
    {
        return 0.0;
    }

    public function getShapImportance(): array
    {
        return [];
    }

    public function getTrustScore(): float
    {
        return $this->trustRepository->getAverageTrustScore();
    }

    public function getTransparencyScore(): float
    {
        return $this->trustRepository->getAverageTransparencyScore();
    }

    public function getDatasetGrowth(): array
    {
        return InteractionEvent::query()
            ->selectRaw('DATE(server_received_at) as event_date, COUNT(*) as event_count')
            ->groupBy('event_date')
            ->orderBy('event_date')
            ->pluck('event_count', 'event_date')
            ->toArray();
    }
}
