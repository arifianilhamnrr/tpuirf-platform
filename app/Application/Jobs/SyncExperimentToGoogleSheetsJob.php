<?php

namespace App\Application\Jobs;

use App\Domain\DTOs\RecommendationDTO;
use App\Infrastructure\Persistence\Models\Experiment;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncExperimentToGoogleSheetsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        public string $userId,
        public string $experimentId,
        public array $recommendationData = []
    ) {}

    public function handle(): void
    {
        $webhookUrl = config('services.google_sheets.webhook_url');
        if (! $webhookUrl) {
            Log::info('Google Sheets sync skipped: GOOGLE_SHEETS_WEBHOOK_URL is not configured.');
            return;
        }

        $user = User::with('profile')->find($this->userId);
        $experiment = Experiment::with(['participantPreference', 'tasks', 'interactionEvents'])->find($this->experimentId);

        if (! $experiment) {
            Log::warning("Google Sheets sync skipped: Experiment {$this->experimentId} not found.");
            return;
        }

        $profile = $user?->profile;
        $preferences = $experiment->participantPreference?->final_config ?? [];
        $events = $experiment->interactionEvents;
        $tasks = $experiment->tasks;

        // Parse top SHAP features summary for spreadsheet column
        $shapSummary = '';
        if (! empty($this->recommendationData['explanations']) && is_array($this->recommendationData['explanations'])) {
            $parts = [];
            foreach (array_slice($this->recommendationData['explanations'], 0, 3) as $exp) {
                $feature = $exp['feature'] ?? 'fitur';
                $impact = isset($exp['impact']) ? number_format((float) $exp['impact'], 3) : '';
                $parts[] = "{$feature} ({$impact})";
            }
            $shapSummary = implode(', ', $parts);
        } elseif (! empty($this->recommendationData['shap_values'])) {
            $shapSummary = json_encode($this->recommendationData['shap_values']);
        }

        $recConfig = $this->recommendationData['recommended_config'] ?? [];
        $confidence = isset($this->recommendationData['confidence_score'])
            ? number_format(((float) $this->recommendationData['confidence_score']) * 100, 1) . '%'
            : '';

        $payload = [
            'timestamp' => now()->toIso8601String(),
            'participant_id' => $user?->id ?? $this->userId,
            'name' => $user?->name ?? 'Participant',
            'birth_date' => $user?->date_of_birth?->format('Y-m-d') ?? '',
            'age' => $profile?->age ?? '',
            'gender' => $profile?->gender ?? '',
            'education' => $profile?->education ?? '',
            'occupation' => $profile?->occupation ?? '',
            'digital_literacy' => $profile?->digital_literacy ?? $profile?->digitalLiteracy ?? '',
            'device_type' => $profile?->device_type ?? $profile?->deviceType ?? '',
            'visual_preference' => $profile?->visual_preference ?? $profile?->visualPreference ?? '',
            'context_environment' => $profile?->context_environment ?? $profile?->contextEnvironment ?? '',
            'ai_familiarity' => $profile?->ai_familiarity ?? $profile?->aiFamiliarity ?? '',
            'daily_device_usage' => $profile?->daily_device_usage ?? $profile?->dailyDeviceUsage ?? '',
            'device_usage_time' => $profile?->device_usage_time ?? $profile?->deviceUsageTime ?? '',
            'primary_device_purpose' => $profile?->primary_device_purpose ?? $profile?->primaryDevicePurpose ?? '',
            'click_count' => $events->where('event_type', 'component_clicked')->count(),
            'scroll_count' => $events->where('event_type', 'scroll_ended')->count(),
            'hover_count' => $events->where('event_type', 'component_hovered')->count(),
            'keyboard_count' => $events->where('input_method', 'keyboard')->count(),
            'avg_task_time' => round((float) ($tasks->avg('active_duration_ms') ?? 0) / 1000, 2),
            'total_session_time' => round((float) ($experiment->total_active_duration_ms ?? 0) / 1000, 2),
            'pref_theme' => $preferences['theme'] ?? '',
            'pref_color_palette' => $preferences['color_palette'] ?? '',
            'pref_font_family' => $preferences['font_family'] ?? '',
            'pref_font_size' => $preferences['font_size'] ?? '',
            'pref_layout_density' => $preferences['layout_density'] ?? '',
            'pref_navigation_position' => $preferences['navigation_position'] ?? '',
            'pref_icon_style' => $preferences['icon_style'] ?? '',
            'pref_design_style' => is_array($preferences['design_styles'] ?? null)
                ? implode(' | ', $preferences['design_styles'])
                : ($preferences['design_style'] ?? ''),
            'ml_model' => $this->recommendationData['model_version'] ?? 'rf+xgboost+shap',
            'pred_design_style' => $recConfig['design_style'] ?? ($this->recommendationData['recommended_ui'] ?? ''),
            'pred_confidence' => $confidence,
            'shap_top_features' => $shapSummary,
        ];

        try {
            $response = Http::timeout(15)
                ->withOptions(['allow_redirects' => true])
                ->post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info("Google Sheets synced successfully for user {$this->userId}. Row: " . ($response->json('row') ?? 'ok'));
            } else {
                Log::error("Google Sheets sync failed with status {$response->status()}: " . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error("Google Sheets sync exception for user {$this->userId}: " . $e->getMessage());
        }
    }
}
