<?php

namespace App\Application\Interfaces;

use App\Domain\DTOs\ExperimentDTO;

interface ExperimentServiceInterface
{
    public function startExperiment(string $userId): ExperimentDTO;
    public function getCurrentExperiment(string $userId): ?ExperimentDTO;
    public function getUiForStep(string $experimentId, int $step): ?array;
    public function completeStep(string $experimentId, array $data): ExperimentDTO;
    public function selectPreferredUi(string $experimentId, string $preferredUi): ExperimentDTO;
    public function completeExperiment(string $experimentId): ExperimentDTO;
    public function getExperimentHistory(string $userId): array;
}
