<?php

namespace App\Domain\Repositories;

use App\Domain\DTOs\RecommendationDTO;

interface RecommendationRepositoryInterface
{
    public function findAll(array $filters = []): array;
    public function findById(string $id): ?RecommendationDTO;
    public function findByUserId(string $userId): array;
    public function create(RecommendationDTO $recommendation): RecommendationDTO;
    public function delete(string $id): bool;
    public function count(): int;
}
