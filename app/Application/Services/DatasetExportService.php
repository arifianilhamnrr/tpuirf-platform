<?php

namespace App\Application\Services;

use App\Application\Interfaces\DatasetExportServiceInterface;
use App\Domain\Repositories\ExperimentRepositoryInterface;
use App\Domain\Repositories\InteractionLogRepositoryInterface;
use App\Domain\Repositories\RecommendationRepositoryInterface;
use App\Domain\Repositories\TrustEvaluationRepositoryInterface;
use App\Domain\Repositories\UserProfileRepositoryInterface;
use App\Infrastructure\Persistence\Models\ExperimentTask;
use App\Infrastructure\Persistence\Models\InteractionEvent;
use App\Infrastructure\Persistence\Models\ParticipantPreference;
use Illuminate\Validation\ValidationException;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class DatasetExportService implements DatasetExportServiceInterface
{
    private const DATASET_TYPES = [
        'full',
        'training',
        'testing',
        'raw-events',
        'task-summary',
        'transitions',
        'preferences',
    ];

    public function __construct(
        private readonly UserProfileRepositoryInterface $profileRepository,
        private readonly InteractionLogRepositoryInterface $logRepository,
        private readonly ExperimentRepositoryInterface $experimentRepository,
        private readonly RecommendationRepositoryInterface $recommendationRepository,
        private readonly TrustEvaluationRepositoryInterface $trustRepository,
    ) {}

    public function exportCsv(string $type): string
    {
        $data = $this->getDataset($type);
        $filename = storage_path("app/exports/{$type}_dataset_".now()->format('Ymd_His').'.csv');

        $directory = dirname($filename);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = fopen($filename, 'w');
        fputcsv($file, $data['headers']);

        foreach ($data['rows'] as $row) {
            fputcsv($file, $this->spreadsheetSafeRow($row));
        }

        fclose($file);

        return $filename;
    }

    public function exportExcel(string $type): string
    {
        $data = $this->getDataset($type);
        $filename = storage_path("app/exports/{$type}_dataset_".now()->format('Ymd_His').'.xlsx');

        $directory = dirname($filename);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $writer = new Writer;
        $writer->openToFile($filename);

        $headerRow = Row::fromValues($data['headers']);
        $writer->addRow($headerRow);

        foreach ($data['rows'] as $row) {
            $writer->addRow(Row::fromValues($this->spreadsheetSafeRow($row)));
        }

        $writer->close();

        return $filename;
    }

    public function exportJson(string $type): string
    {
        $data = $this->getDataset($type);
        $filename = storage_path("app/exports/{$type}_dataset_".now()->format('Ymd_His').'.json');

        $directory = dirname($filename);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $jsonData = [];
        foreach ($data['rows'] as $row) {
            $jsonData[] = array_combine($data['headers'], $row);
        }

        file_put_contents($filename, json_encode($jsonData, JSON_PRETTY_PRINT));

        return $filename;
    }

    public function getTrainingDataset(): array
    {
        return $this->buildMergedDataset('training');
    }

    public function getTestingDataset(): array
    {
        return $this->buildMergedDataset('testing');
    }

    public function getFullDataset(): array
    {
        return $this->buildMergedDataset('full');
    }

    private function getDataset(string $type): array
    {
        if (! in_array($type, self::DATASET_TYPES, true)) {
            throw ValidationException::withMessages([
                'type' => 'Jenis dataset ekspor tidak dikenali.',
            ]);
        }

        return match ($type) {
            'training' => $this->getTrainingDataset(),
            'testing' => $this->getTestingDataset(),
            'raw-events' => $this->buildRawEventsDataset(),
            'task-summary' => $this->buildTaskSummaryDataset(),
            'transitions' => $this->buildTransitionDataset(),
            'preferences' => $this->buildPreferenceDataset(),
            default => $this->getFullDataset(),
        };
    }

    private function buildRawEventsDataset(): array
    {
        $headers = [
            'participant_code', 'protocol_version', 'sequence_number', 'task_key',
            'option_id', 'component_id', 'event_type', 'client_timestamp',
            'server_received_at', 'elapsed_task_ms', 'elapsed_experiment_ms',
            'delta_previous_event_ms', 'delta_previous_click_ms',
            'previous_component_id', 'viewport_width', 'viewport_height',
            'input_method', 'payload',
        ];
        $rows = InteractionEvent::query()
            ->orderBy('experiment_id')
            ->orderBy('sequence_number')
            ->get()
            ->map(fn (InteractionEvent $event) => [
                $event->experiment_id,
                $event->protocol_version,
                $event->sequence_number,
                $event->task_key,
                $event->option_id,
                $event->component_id,
                $event->event_type,
                $event->client_timestamp?->toISOString(),
                $event->server_received_at?->toISOString(),
                $event->elapsed_task_ms,
                $event->elapsed_experiment_ms,
                $event->delta_previous_event_ms,
                $event->delta_previous_click_ms,
                $event->previous_component_id,
                $event->viewport_width,
                $event->viewport_height,
                $event->input_method,
                $event->payload ? json_encode($event->payload) : null,
            ])
            ->values()
            ->toArray();

        return compact('headers', 'rows');
    }

    private function buildTaskSummaryDataset(): array
    {
        $headers = [
            'participant_code', 'protocol_version', 'task_key', 'sequence',
            'status', 'option_order', 'selected_option', 'selection_payload',
            'active_duration_ms', 'idle_duration_ms', 'change_count',
            'started_at', 'completed_at',
        ];
        $rows = ExperimentTask::query()
            ->with('experiment:id,protocol_version')
            ->orderBy('experiment_id')
            ->orderBy('sequence')
            ->get()
            ->map(fn (ExperimentTask $task) => [
                $task->experiment_id,
                $task->experiment?->protocol_version,
                $task->task_key,
                $task->sequence,
                $task->status,
                json_encode($task->option_order),
                $task->selected_option,
                $task->selection_payload ? json_encode($task->selection_payload) : null,
                $task->active_duration_ms,
                $task->idle_duration_ms,
                $task->change_count,
                $task->started_at?->toISOString(),
                $task->completed_at?->toISOString(),
            ])
            ->values()
            ->toArray();

        return compact('headers', 'rows');
    }

    private function buildTransitionDataset(): array
    {
        $headers = [
            'participant_code', 'task_key', 'sequence_number',
            'from_component', 'to_component', 'delta_ms', 'option_id',
        ];
        $rows = InteractionEvent::query()
            ->where('event_type', 'component_clicked')
            ->whereNotNull('previous_component_id')
            ->whereNotNull('component_id')
            ->orderBy('experiment_id')
            ->orderBy('sequence_number')
            ->get()
            ->map(fn (InteractionEvent $event) => [
                $event->experiment_id,
                $event->task_key,
                $event->sequence_number,
                $event->previous_component_id,
                $event->component_id,
                $event->delta_previous_click_ms,
                $event->option_id,
            ])
            ->values()
            ->toArray();

        return compact('headers', 'rows');
    }

    private function buildPreferenceDataset(): array
    {
        $headers = [
            'participant_code', 'protocol_version', 'theme', 'color_palette', 'font_family',
            'font_size', 'layout_density', 'navigation_position', 'icon_style',
            'design_style', 'final_config',
        ];
        $rows = ParticipantPreference::query()
            ->with('experiment:id,protocol_version')
            ->orderBy('experiment_id')
            ->get()
            ->map(fn (ParticipantPreference $preference) => [
                $preference->experiment_id,
                $preference->experiment?->protocol_version,
                $preference->theme,
                $preference->color_palette,
                $preference->font_family,
                $preference->font_size,
                $preference->layout_density,
                $preference->navigation_position,
                $preference->icon_style,
                $preference->design_style,
                $preference->final_config ? json_encode($preference->final_config) : null,
            ])
            ->values()
            ->toArray();

        return compact('headers', 'rows');
    }

    private function spreadsheetSafeRow(array $row): array
    {
        return array_map(static function ($value) {
            if (is_string($value) && preg_match('/^[=+\-@]/', $value)) {
                return "'$value";
            }

            return $value;
        }, $row);
    }

    private function buildMergedDataset(string $split = 'full'): array
    {
        $profiles = $this->profileRepository->findAll();
        $experiments = $this->experimentRepository->findAll();

        $headers = [
            'user_id', 'age', 'gender', 'education', 'occupation',
            'digital_literacy', 'device_type', 'visual_preference',
            'context_environment', 'ai_familiarity',
            'click_count', 'scroll_count', 'hover_count', 'keyboard_count',
            'avg_task_time', 'total_session_time',
            'preferred_ui', 'trust_score', 'transparency_score',
        ];

        $rows = [];

        foreach ($profiles as $profile) {
            $userExperiments = array_filter($experiments, fn ($e) => $e->userId === $profile->userId);

            foreach ($userExperiments as $experiment) {
                if ($experiment->status !== 'completed') {
                    continue;
                }

                $logs = $this->logRepository->findByExperimentId($experiment->id);
                $trustEval = $this->trustRepository->findByExperimentId($experiment->id);

                $clickCount = count(array_filter($logs, fn ($l) => $l->eventType === 'click'));
                $scrollCount = count(array_filter($logs, fn ($l) => $l->eventType === 'scroll'));
                $hoverCount = count(array_filter($logs, fn ($l) => $l->eventType === 'hover'));
                $keyboardCount = count(array_filter($logs, fn ($l) => $l->eventType === 'keyboard'));

                $taskTimes = array_filter(array_map(fn ($l) => $l->taskTime, $logs));
                $avgTaskTime = count($taskTimes) > 0 ? array_sum($taskTimes) / count($taskTimes) : 0;

                $sessionTimes = array_filter(array_map(fn ($l) => $l->sessionTime, $logs));
                $totalSessionTime = count($sessionTimes) > 0 ? max($sessionTimes) : 0;

                $rows[] = [
                    $profile->userId,
                    $profile->age,
                    $profile->gender,
                    $profile->education,
                    $profile->occupation,
                    $profile->digitalLiteracy,
                    $profile->deviceType,
                    $profile->visualPreference,
                    $profile->contextEnvironment,
                    $profile->aiFamiliarity,
                    $clickCount,
                    $scrollCount,
                    $hoverCount,
                    $keyboardCount,
                    round($avgTaskTime, 2),
                    round($totalSessionTime, 2),
                    $experiment->preferredUi,
                    $trustEval['trust_score'] ?? null,
                    $trustEval['transparency_score'] ?? null,
                ];
            }
        }

        if ($split === 'training') {
            $splitIndex = (int) (count($rows) * 0.8);
            $rows = array_slice($rows, 0, $splitIndex);
        } elseif ($split === 'testing') {
            $splitIndex = (int) (count($rows) * 0.8);
            $rows = array_slice($rows, $splitIndex);
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }
}
