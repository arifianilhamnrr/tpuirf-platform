<?php

namespace App\Application\Services;

use App\Application\Interfaces\ExperimentServiceInterface;
use App\Domain\DTOs\ExperimentDTO;
use App\Domain\Repositories\ExperimentRepositoryInterface;
use App\Domain\Repositories\UiTemplateRepositoryInterface;

class ExperimentService implements ExperimentServiceInterface
{
    private const UI_VERSIONS = ['A', 'B', 'C', 'D'];

    public function __construct(
        private readonly ExperimentRepositoryInterface $experimentRepository,
        private readonly UiTemplateRepositoryInterface $uiTemplateRepository,
    ) {}

    public function startExperiment(string $userId): ExperimentDTO
    {
        $existing = $this->experimentRepository->findActiveByUserId($userId);
        if ($existing) {
            return $existing;
        }

        $uiOrder = self::UI_VERSIONS;
        shuffle($uiOrder);

        $experiment = ExperimentDTO::fromArray([
            'user_id' => $userId,
            'status' => 'in_progress',
            'current_ui_version' => $uiOrder[0],
            'ui_order' => $uiOrder,
            'current_step' => 1,
            'started_at' => now()->toDateTimeString(),
        ]);

        return $this->experimentRepository->create($experiment);
    }

    public function getCurrentExperiment(string $userId): ?ExperimentDTO
    {
        return $this->experimentRepository->findActiveByUserId($userId);
    }

    public function getUiForStep(string $experimentId, int $step): ?array
    {
        $experiment = $this->experimentRepository->findById($experimentId);

        if (! $experiment || ! $experiment->uiOrder || ! isset($experiment->uiOrder[$step - 1])) {
            return null;
        }

        $version = $experiment->uiOrder[$step - 1];

        return $this->uiTemplateRepository->findByVersion($version);
    }

    public function completeStep(string $experimentId, array $data): ExperimentDTO
    {
        $experiment = $this->experimentRepository->findById($experimentId);

        if (! $experiment) {
            throw new \RuntimeException('Eksperimen tidak ditemukan');
        }

        $nextStep = $experiment->currentStep + 1;
        $totalSteps = count(self::UI_VERSIONS);

        $updateData = [
            'current_step' => $nextStep,
        ];

        if ($nextStep <= $totalSteps) {
            $updateData['current_ui_version'] = $experiment->uiOrder[$nextStep - 1];
        }

        if ($nextStep > $totalSteps) {
            $updateData['status'] = 'ui_selection';
        }

        return $this->experimentRepository->update(
            $experimentId,
            ExperimentDTO::fromArray(array_merge($experiment->toArray(), $updateData))
        );
    }

    public function selectPreferredUi(string $experimentId, string $preferredUi): ExperimentDTO
    {
        $experiment = $this->experimentRepository->findById($experimentId);

        if (! $experiment) {
            throw new \RuntimeException('Eksperimen tidak ditemukan');
        }

        return $this->experimentRepository->update(
            $experimentId,
            ExperimentDTO::fromArray(array_merge($experiment->toArray(), [
                'preferred_ui' => $preferredUi,
                'status' => 'evaluation',
            ]))
        );
    }

    public function completeExperiment(string $experimentId): ExperimentDTO
    {
        $experiment = $this->experimentRepository->findById($experimentId);

        if (! $experiment) {
            throw new \RuntimeException('Eksperimen tidak ditemukan');
        }

        return $this->experimentRepository->update(
            $experimentId,
            ExperimentDTO::fromArray(array_merge($experiment->toArray(), [
                'status' => 'completed',
                'completed_at' => now()->toDateTimeString(),
            ]))
        );
    }

    public function getExperimentHistory(string $userId): array
    {
        return $this->experimentRepository->findByUserId($userId);
    }
}
