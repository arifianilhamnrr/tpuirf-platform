<?php

namespace App\Application\Services;

use App\Application\Interfaces\InteractionLoggerServiceInterface;
use App\Domain\DTOs\InteractionLogDTO;
use App\Domain\Repositories\InteractionLogRepositoryInterface;

class InteractionLoggerService implements InteractionLoggerServiceInterface
{
    public function __construct(
        private readonly InteractionLogRepositoryInterface $logRepository,
    ) {}

    public function log(array $data): InteractionLogDTO
    {
        return $this->logRepository->create(InteractionLogDTO::fromArray($data));
    }

    public function getLogsByExperiment(string $experimentId): array
    {
        return $this->logRepository->findByExperimentId($experimentId);
    }

    public function getLogsByUser(string $userId): array
    {
        return $this->logRepository->findByUserId($userId);
    }

    public function getAggregatedMetrics(string $experimentId): array
    {
        return $this->logRepository->getAggregatedData($experimentId);
    }

    public function bulkCreate(array $logs): array
    {
        $created = [];
        foreach ($logs as $log) {
            $created[] = $this->log($log);
        }
        return $created;
    }
}
