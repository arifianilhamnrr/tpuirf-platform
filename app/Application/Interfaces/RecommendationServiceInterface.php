<?php

namespace App\Application\Interfaces;

use App\Domain\DTOs\RecommendationDTO;

interface RecommendationServiceInterface
{
    public function generateRecommendation(string $userId): RecommendationDTO;
    public function generateForExperiment(string $userId, string $experimentId): RecommendationDTO;
    public function getRecommendation(string $userId): ?RecommendationDTO;
    public function trainModel(): bool;
    public function getModelAccuracy(): float;
    public function getModelStatus(): array;
    public function getFeatureImportance(): array;
}
