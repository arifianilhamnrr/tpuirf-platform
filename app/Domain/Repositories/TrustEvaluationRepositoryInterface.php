<?php

namespace App\Domain\Repositories;

interface TrustEvaluationRepositoryInterface
{
    public function findAll(array $filters = []): array;
    public function findById(string $id): ?array;
    public function findByUserId(string $userId): array;
    public function findByExperimentId(string $experimentId): ?array;
    public function create(array $data): array;
    public function update(string $id, array $data): array;
    public function delete(string $id): bool;
    public function count(): int;
    public function getAverageTrustScore(): float;
    public function getAverageTransparencyScore(): float;
}
