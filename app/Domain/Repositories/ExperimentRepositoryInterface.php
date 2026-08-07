<?php

namespace App\Domain\Repositories;

use App\Domain\DTOs\ExperimentDTO;

interface ExperimentRepositoryInterface
{
    public function findAll(array $filters = []): array;

    public function findById(string $id): ?ExperimentDTO;

    public function findByUserId(string $userId): array;

    public function findActiveByUserId(string $userId): ?ExperimentDTO;

    public function create(ExperimentDTO $experiment): ExperimentDTO;

    public function update(string $id, ExperimentDTO $experiment): ExperimentDTO;

    public function delete(string $id): bool;

    public function count(array $filters = []): int;
}
