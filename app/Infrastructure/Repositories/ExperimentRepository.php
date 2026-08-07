<?php

namespace App\Infrastructure\Repositories;

use App\Domain\DTOs\ExperimentDTO;
use App\Domain\Repositories\ExperimentRepositoryInterface;
use App\Infrastructure\Persistence\Models\Experiment;

class ExperimentRepository implements ExperimentRepositoryInterface
{
    public function findAll(array $filters = []): array
    {
        $query = Experiment::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (Experiment $e) => $this->toDTO($e))
            ->toArray();
    }

    public function findById(string $id): ?ExperimentDTO
    {
        $experiment = Experiment::find($id);

        return $experiment ? $this->toDTO($experiment) : null;
    }

    public function findByUserId(string $userId): array
    {
        return Experiment::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (Experiment $e) => $this->toDTO($e))
            ->toArray();
    }

    public function findActiveByUserId(string $userId): ?ExperimentDTO
    {
        $experiment = Experiment::where('user_id', $userId)
            ->whereIn('status', [
                'pending',
                'consented',
                'profiling',
                'in_progress',
                'ui_selection',
                'evaluation',
            ])
            ->first();

        return $experiment ? $this->toDTO($experiment) : null;
    }

    public function create(ExperimentDTO $experiment): ExperimentDTO
    {
        $model = Experiment::create($experiment->toArray());

        return $this->toDTO($model);
    }

    public function update(string $id, ExperimentDTO $experiment): ExperimentDTO
    {
        $model = Experiment::findOrFail($id);
        $model->update($experiment->toArray());

        return $this->toDTO($model->fresh());
    }

    public function delete(string $id): bool
    {
        return Experiment::findOrFail($id)->delete();
    }

    public function count(array $filters = []): int
    {
        $query = Experiment::query();
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->count();
    }

    private function toDTO(Experiment $experiment): ExperimentDTO
    {
        return ExperimentDTO::fromArray($experiment->toArray());
    }
}
