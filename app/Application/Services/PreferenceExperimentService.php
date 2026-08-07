<?php

namespace App\Application\Services;

use App\Application\Interfaces\PreferenceExperimentServiceInterface;
use App\Infrastructure\Persistence\Models\Experiment;
use App\Infrastructure\Persistence\Models\ExperimentTask;
use App\Infrastructure\Persistence\Models\InteractionEvent;
use App\Infrastructure\Persistence\Models\ParticipantPreference;
use App\Infrastructure\Persistence\Models\TrustEvaluation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PreferenceExperimentService implements PreferenceExperimentServiceInterface
{
    public function findForUser(string $userId): ?array
    {
        $experiment = Experiment::query()
            ->where('user_id', $userId)
            ->where('protocol_version', config('experiment.protocol_version'))
            ->latest('created_at')
            ->first();

        return $experiment ? $this->experimentState($experiment) : null;
    }

    public function start(string $userId, string $consentVersion): array
    {
        return DB::transaction(function () use ($userId, $consentVersion): array {
            $protocolVersion = (string) config('experiment.protocol_version');
            $experiment = Experiment::query()
                ->where('user_id', $userId)
                ->where('protocol_version', $protocolVersion)
                ->lockForUpdate()
                ->first();

            if ($experiment) {
                return $this->experimentState($experiment);
            }

            $taskDefinitions = config('experiment.tasks', []);
            $taskOrder = array_keys($taskDefinitions);

            if ($taskOrder === []) {
                throw ValidationException::withMessages([
                    'protocol' => 'Konfigurasi tugas eksperimen masih kosong.',
                ]);
            }

            $experiment = Experiment::create([
                'user_id' => $userId,
                'status' => 'in_progress',
                'protocol_version' => $protocolVersion,
                'current_task' => $taskOrder[0],
                'task_order' => $taskOrder,
                'current_step' => 1,
                'started_at' => now(),
                'last_activity_at' => now(),
                'consent_version' => $consentVersion,
                'consented_at' => now(),
            ]);

            foreach ($taskOrder as $index => $taskKey) {
                ExperimentTask::create([
                    'experiment_id' => $experiment->id,
                    'task_key' => $taskKey,
                    'sequence' => $index + 1,
                    'status' => 'pending',
                    'option_order' => $this->randomizedOptions($taskDefinitions[$taskKey]['options']),
                ]);
            }

            ParticipantPreference::create([
                'experiment_id' => $experiment->id,
                'user_id' => $userId,
                'final_config' => [],
            ]);

            $this->appendServerEvent($experiment, 'experiment_started', null, [
                'consent_version' => $consentVersion,
            ], 0);

            return $this->experimentState($experiment->fresh());
        });
    }

    public function getState(string $userId, string $experimentId): array
    {
        return $this->experimentState($this->ownedExperiment($userId, $experimentId));
    }

    public function getTask(string $userId, string $experimentId, string $taskKey): array
    {
        return DB::transaction(function () use ($userId, $experimentId, $taskKey): array {
            $experiment = $this->ownedExperiment($userId, $experimentId, true);
            $task = $this->taskForExperiment($experiment, $taskKey, true);

            if ($task->status === 'completed') {
                return $this->taskState($experiment, $task);
            }

            if ($experiment->status !== 'in_progress' || $experiment->current_task !== $taskKey) {
                throw ValidationException::withMessages([
                    'task_key' => 'Tugas ini belum tersedia saat ini.',
                ]);
            }

            if ($task->status === 'pending') {
                $task->update([
                    'status' => 'in_progress',
                    'started_at' => now(),
                ]);
            }

            $experiment->update(['last_activity_at' => now()]);

            return $this->taskState($experiment->fresh(), $task->fresh());
        });
    }

    public function submitSelection(
        string $userId,
        string $experimentId,
        string $taskKey,
        array $selection,
    ): array {
        return DB::transaction(function () use ($userId, $experimentId, $taskKey, $selection): array {
            $experiment = $this->ownedExperiment($userId, $experimentId, true);
            $task = $this->taskForExperiment($experiment, $taskKey, true);
            $normalized = $this->normalizeSelection($taskKey, $selection);

            if ($task->status === 'completed') {
                if ($task->selection_payload === $normalized['payload']) {
                    return $this->experimentState($experiment);
                }

                $task->update([
                    'selected_option' => $normalized['selected_option'],
                    'selection_payload' => $normalized['payload'],
                    'active_duration_ms' => $task->active_duration_ms + $selection['active_duration_ms'],
                    'idle_duration_ms' => $task->idle_duration_ms + ($selection['idle_duration_ms'] ?? 0),
                    'change_count' => $task->change_count + ($selection['change_count'] ?? 0),
                    'completed_at' => now(),
                ]);

                $this->savePreference($experiment, $taskKey, $normalized['payload']);
                $this->appendServerEvent($experiment, 'task_revised', $task->fresh(), [
                    'selection' => $normalized['payload'],
                    'active_duration_ms' => $selection['active_duration_ms'],
                    'idle_duration_ms' => $selection['idle_duration_ms'] ?? 0,
                    'change_count' => $selection['change_count'] ?? 0,
                ]);

                $experiment->update([
                    'last_activity_at' => now(),
                    'total_active_duration_ms' => $experiment->tasks()->sum('active_duration_ms'),
                ]);

                return $this->experimentState($experiment->fresh());
            }

            if ($experiment->status !== 'in_progress' || $experiment->current_task !== $taskKey) {
                throw ValidationException::withMessages([
                    'task_key' => 'Tugas harus diselesaikan sesuai urutan yang ditentukan.',
                ]);
            }

            $task->update([
                'status' => 'completed',
                'selected_option' => $normalized['selected_option'],
                'selection_payload' => $normalized['payload'],
                'active_duration_ms' => $selection['active_duration_ms'],
                'idle_duration_ms' => $selection['idle_duration_ms'] ?? 0,
                'change_count' => $selection['change_count'] ?? 0,
                'started_at' => $task->started_at ?? now(),
                'completed_at' => now(),
            ]);

            $this->savePreference($experiment, $taskKey, $normalized['payload']);
            $this->appendServerEvent($experiment, 'task_completed', $task, [
                'selection' => $normalized['payload'],
                'active_duration_ms' => $selection['active_duration_ms'],
                'idle_duration_ms' => $selection['idle_duration_ms'] ?? 0,
                'change_count' => $selection['change_count'] ?? 0,
            ]);

            $taskOrder = $experiment->task_order;
            $currentIndex = array_search($taskKey, $taskOrder, true);
            $nextTask = $currentIndex === false ? null : ($taskOrder[$currentIndex + 1] ?? null);
            $totalActiveDuration = $experiment->tasks()->sum('active_duration_ms');

            $experiment->update([
                'status' => $nextTask ? 'in_progress' : 'evaluation',
                'current_task' => $nextTask,
                'current_step' => $nextTask ? $currentIndex + 2 : count($taskOrder) + 1,
                'last_activity_at' => now(),
                'total_active_duration_ms' => $totalActiveDuration,
            ]);

            return $this->experimentState($experiment->fresh());
        });
    }

    public function recordEvents(string $userId, string $experimentId, array $events): array
    {
        return DB::transaction(function () use ($userId, $experimentId, $events): array {
            $experiment = $this->ownedExperiment($userId, $experimentId, true);

            if (in_array($experiment->status, ['completed', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'experiment_id' => 'Event tidak dapat ditambahkan ke eksperimen yang sudah ditutup.',
                ]);
            }

            $taskIds = $experiment->tasks()->pluck('id', 'task_key');
            $receivedAt = now();
            $rows = [];
            $duplicates = 0;
            $maximumSequence = (int) ($experiment->interactionEvents()->max('sequence_number') ?? 0);
            $occupiedSequences = $experiment->interactionEvents()
                ->pluck('event_uuid', 'sequence_number');
            $existingByUuid = InteractionEvent::query()
                ->whereIn('event_uuid', array_column($events, 'event_uuid'))
                ->get(['event_uuid', 'experiment_id'])
                ->keyBy('event_uuid');

            foreach ($events as $event) {
                $taskKey = $event['task_key'] ?? null;
                $eventUuid = $event['event_uuid'];
                $existingEvent = $existingByUuid->get($eventUuid);

                if ($existingEvent) {
                    if ($existingEvent->experiment_id !== $experiment->id) {
                        throw ValidationException::withMessages([
                            'events' => "UUID event [$eventUuid] sudah dimiliki eksperimen lain.",
                        ]);
                    }

                    $duplicates++;
                    continue;
                }

                if ($taskKey !== null && ! $taskIds->has($taskKey)) {
                    throw ValidationException::withMessages([
                        'events' => "Tugas [$taskKey] bukan bagian dari eksperimen ini.",
                    ]);
                }

                $clientSequence = (int) $event['sequence_number'];
                $sequenceNumber = $clientSequence;
                $payload = $event['payload'] ?? [];

                if ($occupiedSequences->has($sequenceNumber) || $sequenceNumber <= $maximumSequence) {
                    $sequenceNumber = ++$maximumSequence;
                    $payload['_client_sequence_number'] = $clientSequence;
                } else {
                    $maximumSequence = $sequenceNumber;
                }

                $occupiedSequences->put($sequenceNumber, $eventUuid);
                $rows[] = [
                    'id' => (string) Str::uuid(),
                    'event_uuid' => $eventUuid,
                    'experiment_id' => $experiment->id,
                    'user_id' => $userId,
                    'experiment_task_id' => $taskKey ? $taskIds->get($taskKey) : null,
                    'sequence_number' => $sequenceNumber,
                    'protocol_version' => $experiment->protocol_version,
                    'task_key' => $taskKey,
                    'option_id' => $event['option_id'] ?? null,
                    'component_id' => $event['component_id'] ?? null,
                    'event_type' => $event['event_type'],
                    'client_timestamp' => isset($event['client_timestamp'])
                        ? Carbon::parse($event['client_timestamp'])->utc()
                        : null,
                    'server_received_at' => $receivedAt,
                    'elapsed_task_ms' => $event['elapsed_task_ms'] ?? null,
                    'elapsed_experiment_ms' => $event['elapsed_experiment_ms'] ?? null,
                    'delta_previous_event_ms' => $event['delta_previous_event_ms'] ?? null,
                    'delta_previous_click_ms' => $event['delta_previous_click_ms'] ?? null,
                    'previous_component_id' => $event['previous_component_id'] ?? null,
                    'viewport_width' => $event['viewport_width'] ?? null,
                    'viewport_height' => $event['viewport_height'] ?? null,
                    'input_method' => $event['input_method'] ?? 'unknown',
                    'payload' => $payload === [] ? null : json_encode($payload),
                    'created_at' => $receivedAt,
                    'updated_at' => $receivedAt,
                ];
            }

            if ($rows !== []) {
                InteractionEvent::query()->insert($rows);
            }
            $experiment->update(['last_activity_at' => $receivedAt]);

            return [
                'received' => count($events),
                'inserted' => count($rows),
                'duplicates' => $duplicates,
                'last_sequence_number' => $maximumSequence,
            ];
        });
    }

    public function saveEvaluation(string $userId, string $experimentId, array $evaluation): array
    {
        return DB::transaction(function () use ($userId, $experimentId, $evaluation): array {
            $experiment = $this->ownedExperiment($userId, $experimentId, true);

            if ($experiment->status !== 'evaluation') {
                throw ValidationException::withMessages([
                    'experiment_id' => 'Seluruh tugas preferensi harus diselesaikan sebelum evaluasi.',
                ]);
            }

            $evaluationAlreadySubmitted = TrustEvaluation::query()
                ->where('experiment_id', $experiment->id)
                ->exists();

            $trustEvaluation = TrustEvaluation::query()->updateOrCreate(
                ['experiment_id' => $experiment->id],
                [
                    'user_id' => $userId,
                    'trust_score' => $evaluation['trust_score'],
                    'transparency_score' => $evaluation['transparency_score'],
                    'ui_satisfaction' => $evaluation['ui_satisfaction'],
                    'recommendation_quality' => $evaluation['recommendation_quality'] ?? null,
                    'feedback' => $evaluation['feedback'] ?? null,
                    'additional_data' => $evaluation['additional_data'] ?? null,
                ],
            );

            $experiment->update(['last_activity_at' => now()]);
            if (! $evaluationAlreadySubmitted) {
                $this->appendServerEvent($experiment, 'evaluation_submitted', null, [
                    'trust_score' => $evaluation['trust_score'],
                    'transparency_score' => $evaluation['transparency_score'],
                    'ui_satisfaction' => $evaluation['ui_satisfaction'],
                ]);
            }

            return $trustEvaluation->fresh()->toArray();
        });
    }

    public function complete(string $userId, string $experimentId): array
    {
        return DB::transaction(function () use ($userId, $experimentId): array {
            $experiment = $this->ownedExperiment($userId, $experimentId, true);

            if ($experiment->status === 'completed') {
                return $this->experimentState($experiment);
            }

            $taskCount = count($experiment->task_order ?? []);
            $completedTaskCount = $experiment->tasks()->where('status', 'completed')->count();

            if ($taskCount === 0 || $completedTaskCount !== $taskCount) {
                throw ValidationException::withMessages([
                    'experiment_id' => 'Seluruh tugas preferensi harus diselesaikan terlebih dahulu.',
                ]);
            }

            if (! $experiment->trustEvaluation()->exists()) {
                throw ValidationException::withMessages([
                    'evaluation' => 'Evaluasi harus dikirim sebelum eksperimen diselesaikan.',
                ]);
            }

            $this->appendServerEvent($experiment, 'experiment_completed', null, [
                'total_active_duration_ms' => $experiment->total_active_duration_ms,
            ]);

            $experiment->update([
                'status' => 'completed',
                'current_task' => null,
                'completed_at' => now(),
                'last_activity_at' => now(),
            ]);

            return $this->experimentState($experiment->fresh());
        });
    }

    private function ownedExperiment(string $userId, string $experimentId, bool $lock = false): Experiment
    {
        $query = Experiment::query()
            ->whereKey($experimentId)
            ->where('user_id', $userId)
            ->where('protocol_version', config('experiment.protocol_version'));

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->firstOrFail();
    }

    private function taskForExperiment(Experiment $experiment, string $taskKey, bool $lock = false): ExperimentTask
    {
        $query = $experiment->tasks()->where('task_key', $taskKey);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->firstOrFail();
    }

    private function randomizedOptions(array $options): array
    {
        if (array_is_list($options)) {
            shuffle($options);

            return $options;
        }

        foreach ($options as $key => $values) {
            shuffle($values);
            $options[$key] = $values;
        }

        return $options;
    }

    private function normalizeSelection(string $taskKey, array $selection): array
    {
        $definition = config("experiment.tasks.$taskKey");

        if (! $definition) {
            throw ValidationException::withMessages(['task_key' => 'Tugas eksperimen tidak dikenali.']);
        }

        $payload = $selection['selection_payload'] ?? [];

        if ($taskKey === 'typography') {
            $fontFamily = $payload['font_family'] ?? null;
            $fontSize = $payload['font_size'] ?? null;

            if (! in_array($fontFamily, $definition['options']['font_family'], true)
                || ! in_array($fontSize, $definition['options']['font_size'], true)) {
                throw ValidationException::withMessages([
                    'selection_payload' => 'Pilihan tipografi tidak valid.',
                ]);
            }

            return [
                'selected_option' => "$fontFamily:$fontSize",
                'payload' => [
                    'font_family' => $fontFamily,
                    'font_size' => $fontSize,
                ],
            ];
        }

        if ($taskKey === 'design_style') {
            $designStyles = $payload['design_styles'] ?? [];

            if (! is_array($designStyles)
                || $designStyles === []
                || count($designStyles) !== count(array_unique($designStyles))
                || array_diff($designStyles, $definition['options']) !== []) {
                throw ValidationException::withMessages([
                    'selection_payload.design_styles' => 'Pilih minimal satu gaya desain yang valid.',
                ]);
            }

            return [
                'selected_option' => implode('|', $designStyles),
                'payload' => ['design_styles' => array_values($designStyles)],
            ];
        }

        $selectedOption = $selection['selected_option'] ?? null;

        if (! in_array($selectedOption, $definition['options'], true)) {
            throw ValidationException::withMessages([
                'selected_option' => 'Opsi yang dipilih tidak valid untuk tugas ini.',
            ]);
        }

        return [
            'selected_option' => $selectedOption,
            'payload' => [$taskKey => $selectedOption],
        ];
    }

    private function savePreference(Experiment $experiment, string $taskKey, array $payload): void
    {
        $fields = match ($taskKey) {
            'theme' => ['theme' => $payload['theme']],
            'typography' => [
                'font_family' => $payload['font_family'],
                'font_size' => $payload['font_size'],
            ],
            'layout_density' => ['layout_density' => $payload['layout_density']],
            'navigation_position' => ['navigation_position' => $payload['navigation_position']],
            'icon_style' => ['icon_style' => $payload['icon_style']],
            'design_style' => ['design_style' => implode('|', $payload['design_styles'])],
            default => [],
        };

        $preference = ParticipantPreference::query()->firstOrCreate(
            ['experiment_id' => $experiment->id],
            ['user_id' => $experiment->user_id],
        );
        $preference->fill($fields);
        $preference->final_config = array_filter([
            'theme' => $preference->theme,
            'font_family' => $preference->font_family,
            'font_size' => $preference->font_size,
            'layout_density' => $preference->layout_density,
            'navigation_position' => $preference->navigation_position,
            'icon_style' => $preference->icon_style,
            'design_styles' => $taskKey === 'design_style'
                ? $payload['design_styles']
                : ($preference->final_config['design_styles'] ?? null),
        ], static fn ($value) => $value !== null);
        $preference->save();
    }

    private function experimentState(Experiment $experiment): array
    {
        $tasks = [];

        foreach ($experiment->tasks()->get() as $task) {
            $tasks[] = [
                'task_key' => $task->task_key,
                'sequence' => $task->sequence,
                'status' => $task->status,
            ];
        }

        return [
            'id' => $experiment->id,
            'protocol_version' => $experiment->protocol_version,
            'status' => $experiment->status,
            'current_task' => $experiment->current_task,
            'task_order' => $experiment->task_order,
            'current_step' => $experiment->current_step,
            'total_active_duration_ms' => $experiment->total_active_duration_ms,
            'started_at' => $experiment->started_at?->toISOString(),
            'completed_at' => $experiment->completed_at?->toISOString(),
            'last_event_sequence' => (int) ($experiment->interactionEvents()->max('sequence_number') ?? 0),
            'tasks' => $tasks,
            'preferences' => $experiment->participantPreference?->final_config ?? [],
        ];
    }

    private function taskState(Experiment $experiment, ExperimentTask $task): array
    {
        $latestDraft = $task->interactionEvents()
            ->where('event_type', 'selection_changed')
            ->latest('sequence_number')
            ->first(['option_id', 'payload']);

        return [
            'experiment_id' => $experiment->id,
            'protocol_version' => $experiment->protocol_version,
            'task_key' => $task->task_key,
            'sequence' => $task->sequence,
            'status' => $task->status,
            'option_order' => $task->option_order,
            'selected_option' => $task->selected_option,
            'selection_payload' => $task->selection_payload,
            'draft_option' => $latestDraft?->option_id,
            'draft_selection_payload' => $latestDraft?->payload ?? [],
            'started_at' => $task->started_at?->toISOString(),
        ];
    }

    private function appendServerEvent(
        Experiment $experiment,
        string $eventType,
        ?ExperimentTask $task = null,
        array $payload = [],
        ?int $sequenceNumber = null,
    ): void {
        $receivedAt = now();
        $sequenceNumber ??= ((int) $experiment->interactionEvents()->max('sequence_number')) + 1;

        InteractionEvent::create([
            'event_uuid' => (string) Str::uuid(),
            'experiment_id' => $experiment->id,
            'user_id' => $experiment->user_id,
            'experiment_task_id' => $task?->id,
            'sequence_number' => $sequenceNumber,
            'protocol_version' => $experiment->protocol_version,
            'task_key' => $task?->task_key,
            'option_id' => $task?->selected_option,
            'event_type' => $eventType,
            'server_received_at' => $receivedAt,
            'elapsed_experiment_ms' => $experiment->started_at
                ? $experiment->started_at->diffInMilliseconds($receivedAt)
                : null,
            'input_method' => 'unknown',
            'payload' => $payload,
        ]);
    }
}
