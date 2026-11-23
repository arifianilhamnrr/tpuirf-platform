<?php

namespace App\Application\Jobs;

use App\Infrastructure\Persistence\Models\XaiEvaluation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncXaiEvaluationToGoogleSheetsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(public string $xaiEvaluationId) {}

    public function handle(): void
    {
        $webhookUrl = config('services.google_sheets.webhook_url');
        $evaluation = XaiEvaluation::with('recommendation')->find($this->xaiEvaluationId);
        if (! $webhookUrl || ! $evaluation) {
            return;
        }

        $payload = [
            'record_type' => 'xai_evaluation',
            'participant_id' => $evaluation->user_id,
            'experiment_id' => $evaluation->experiment_id,
            'recommendation_id' => $evaluation->recommendation_id,
            'model_version' => $evaluation->recommendation?->model_version,
            'instrument_version' => $evaluation->instrument_version,
            'submitted_at' => $evaluation->submitted_at?->toIso8601String(),
        ];
        foreach (array_keys(config('xai_evaluation.items')) as $key) {
            $payload[$key] = $evaluation->{$key};
        }

        try {
            $response = Http::timeout(15)->withOptions(['allow_redirects' => true])->post($webhookUrl, $payload);
            if (! $response->successful()) {
                throw new \RuntimeException("Google Sheets returned {$response->status()}");
            }
        } catch (\Throwable $e) {
            Log::error('XAI evaluation Google Sheets sync failed.', [
                'xai_evaluation_id' => $this->xaiEvaluationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
