<?php

namespace App\Application\Interfaces;

interface AnalyticsServiceInterface
{
    public function getDashboardStats(): array;

    public function getRespondentGrowth(): array;

    public function getInteractionHeatmap(?string $experimentId = null): array;

    public function getClickDistribution(?string $experimentId = null): array;

    public function getTaskDurationStats(): array;

    public function getRecommendationAccuracy(): float;

    public function getShapImportance(): array;

    public function getTrustScore(): float;

    public function getTransparencyScore(): float;

    public function getDatasetGrowth(): array;

    public function getXaiEvaluationStats(): array;
}
