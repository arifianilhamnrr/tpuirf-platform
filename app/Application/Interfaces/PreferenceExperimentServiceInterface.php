<?php

namespace App\Application\Interfaces;

interface PreferenceExperimentServiceInterface
{
    public function findForUser(string $userId): ?array;

    public function start(string $userId, string $consentVersion): array;

    public function getState(string $userId, string $experimentId): array;

    public function getTask(string $userId, string $experimentId, string $taskKey): array;

    public function submitSelection(
        string $userId,
        string $experimentId,
        string $taskKey,
        array $selection,
    ): array;

    public function recordEvents(string $userId, string $experimentId, array $events): array;

    public function saveEvaluation(string $userId, string $experimentId, array $evaluation): array;

    public function complete(string $userId, string $experimentId): array;
}
