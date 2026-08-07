<?php

namespace App\Infrastructure\Repositories;

use App\Domain\DTOs\RecommendationDTO;
use App\Domain\Repositories\RecommendationRepositoryInterface;
use App\Infrastructure\Persistence\Models\Recommendation;

class RecommendationRepository implements RecommendationRepositoryInterface
{
    public function findAll(array $filters = []): array
    {
        $query = Recommendation::query();
        
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        return $query->orderBy('created_at', 'desc')
            ->get()
            ->map(fn(Recommendation $r) => $this->toDTO($r))
            ->toArray();
    }

    public function findById(string $id): ?RecommendationDTO
    {
        $recommendation = Recommendation::find($id);
        return $recommendation ? $this->toDTO($recommendation) : null;
    }

    public function findByUserId(string $userId): array
    {
        return Recommendation::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn(Recommendation $r) => $this->toDTO($r))
            ->toArray();
    }

    public function create(RecommendationDTO $recommendation): RecommendationDTO
    {
        $model = Recommendation::create($recommendation->toArray());
        return $this->toDTO($model);
    }

    public function delete(string $id): bool
    {
        return Recommendation::findOrFail($id)->delete();
    }

    public function count(): int
    {
        return Recommendation::count();
    }

    private function toDTO(Recommendation $recommendation): RecommendationDTO
    {
        return RecommendationDTO::fromArray($recommendation->toArray());
    }
}
