<?php

namespace App\Application\Interfaces;

use App\Domain\DTOs\InteractionLogDTO;

interface InteractionLoggerServiceInterface
{
    public function log(array $data): InteractionLogDTO;
    public function getLogsByExperiment(string $experimentId): array;
    public function getLogsByUser(string $userId): array;
    public function getAggregatedMetrics(string $experimentId): array;
    public function bulkCreate(array $logs): array;
}
