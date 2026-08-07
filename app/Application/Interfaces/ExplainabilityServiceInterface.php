<?php

namespace App\Application\Interfaces;

interface ExplainabilityServiceInterface
{
    public function getExplanation(string $recommendationId): array;
    public function getHumanReadableExplanation(array $shapValues, array $features): array;
    public function getFeatureImportance(): array;
}
