<?php

namespace App\Domain\Repositories;

use App\Domain\DTOs\InteractionLogDTO;

interface InteractionLogRepositoryInterface
{
    public function findAll(array $filters = []): array;
    public function findById(string $id): ?InteractionLogDTO;
    public function findByExperimentId(string $experimentId): array;
    public function findByUserId(string $userId): array;
    public function create(InteractionLogDTO $log): InteractionLogDTO;
    public function delete(string $id): bool;
    public function count(array $filters = []): int;
    public function getAggregatedData(string $experimentId): array;
}
