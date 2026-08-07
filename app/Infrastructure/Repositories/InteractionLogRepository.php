<?php

namespace App\Infrastructure\Repositories;

use App\Domain\DTOs\InteractionLogDTO;
use App\Domain\Repositories\InteractionLogRepositoryInterface;
use App\Infrastructure\Persistence\Models\InteractionLog;
use Illuminate\Support\Facades\DB;

class InteractionLogRepository implements InteractionLogRepositoryInterface
{
    public function findAll(array $filters = []): array
    {
        $query = InteractionLog::query();
        
        if (isset($filters['experiment_id'])) {
            $query->where('experiment_id', $filters['experiment_id']);
        }
        
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        if (isset($filters['ui_version'])) {
            $query->where('ui_version', $filters['ui_version']);
        }
        
        if (isset($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }
        
        return $query->orderBy('timestamp', 'asc')
            ->get()
            ->map(fn(InteractionLog $log) => $this->toDTO($log))
            ->toArray();
    }

    public function findById(string $id): ?InteractionLogDTO
    {
        $log = InteractionLog::find($id);
        return $log ? $this->toDTO($log) : null;
    }

    public function findByExperimentId(string $experimentId): array
    {
        return InteractionLog::where('experiment_id', $experimentId)
            ->orderBy('timestamp', 'asc')
            ->get()
            ->map(fn(InteractionLog $log) => $this->toDTO($log))
            ->toArray();
    }

    public function findByUserId(string $userId): array
    {
        return InteractionLog::where('user_id', $userId)
            ->orderBy('timestamp', 'desc')
            ->get()
            ->map(fn(InteractionLog $log) => $this->toDTO($log))
            ->toArray();
    }

    public function create(InteractionLogDTO $log): InteractionLogDTO
    {
        $model = InteractionLog::create($log->toArray());
        return $this->toDTO($model);
    }

    public function delete(string $id): bool
    {
        return InteractionLog::findOrFail($id)->delete();
    }

    public function count(array $filters = []): int
    {
        $query = InteractionLog::query();
        if (isset($filters['experiment_id'])) {
            $query->where('experiment_id', $filters['experiment_id']);
        }
        return $query->count();
    }

    public function getAggregatedData(string $experimentId): array
    {
        return InteractionLog::where('experiment_id', $experimentId)
            ->select('event_type', DB::raw('count(*) as count'), DB::raw('AVG(task_time) as avg_task_time'))
            ->groupBy('event_type')
            ->get()
            ->toArray();
    }

    private function toDTO(InteractionLog $log): InteractionLogDTO
    {
        return InteractionLogDTO::fromArray($log->toArray());
    }
}
