<?php

namespace App\Domain\DTOs;

class ExperimentDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $userId,
        public readonly string $status,
        public readonly ?string $currentUiVersion,
        public readonly ?array $uiOrder,
        public readonly ?int $currentStep,
        public readonly ?string $startedAt,
        public readonly ?string $completedAt,
        public readonly ?string $preferredUi,
        public readonly ?array $metadata,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            userId: $data['user_id'],
            status: $data['status'] ?? 'pending',
            currentUiVersion: $data['current_ui_version'] ?? null,
            uiOrder: $data['ui_order'] ?? null,
            currentStep: $data['current_step'] ?? 0,
            startedAt: $data['started_at'] ?? null,
            completedAt: $data['completed_at'] ?? null,
            preferredUi: $data['preferred_ui'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'status' => $this->status,
            'current_ui_version' => $this->currentUiVersion,
            'ui_order' => $this->uiOrder,
            'current_step' => $this->currentStep,
            'started_at' => $this->startedAt,
            'completed_at' => $this->completedAt,
            'preferred_ui' => $this->preferredUi,
            'metadata' => $this->metadata,
        ];
    }
}
