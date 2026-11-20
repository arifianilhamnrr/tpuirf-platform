<?php

namespace App\Application\Jobs;

use App\Application\Interfaces\RecommendationServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetrainRecommendationModelJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 15;
    public int $uniqueFor = 300;

    public function __construct(public string $sourceExperimentId) {}

    public function uniqueId(): string
    {
        return 'tpuirf-retrain';
    }

    public function handle(RecommendationServiceInterface $recommendationService): void
    {
        if (! $recommendationService->trainModel()) {
            throw new \RuntimeException('XGBoost retraining failed. Previous active model remains in service.');
        }

        Log::info('XGBoost retraining completed.', [
            'source_experiment_id' => $this->sourceExperimentId,
        ]);
    }
}
