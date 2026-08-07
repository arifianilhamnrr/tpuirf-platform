<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\TrustEvaluationRepositoryInterface;
use App\Infrastructure\Persistence\Models\TrustEvaluation;

class TrustEvaluationRepository implements TrustEvaluationRepositoryInterface
{
    public function findAll(array $filters = []): array
    {
        $query = TrustEvaluation::query();
        
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        return $query->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function findById(string $id): ?array
    {
        $eval = TrustEvaluation::find($id);
        return $eval ? $eval->toArray() : null;
    }

    public function findByUserId(string $userId): array
    {
        return TrustEvaluation::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function findByExperimentId(string $experimentId): ?array
    {
        $eval = TrustEvaluation::where('experiment_id', $experimentId)->first();
        return $eval ? $eval->toArray() : null;
    }

    public function create(array $data): array
    {
        return TrustEvaluation::create($data)->toArray();
    }

    public function update(string $id, array $data): array
    {
        $eval = TrustEvaluation::findOrFail($id);
        $eval->update($data);
        return $eval->fresh()->toArray();
    }

    public function delete(string $id): bool
    {
        return TrustEvaluation::findOrFail($id)->delete();
    }

    public function count(): int
    {
        return TrustEvaluation::count();
    }

    public function getAverageTrustScore(): float
    {
        return TrustEvaluation::avg('trust_score') ?? 0.0;
    }

    public function getAverageTransparencyScore(): float
    {
        return TrustEvaluation::avg('transparency_score') ?? 0.0;
    }
}
