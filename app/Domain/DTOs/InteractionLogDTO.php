<?php

namespace App\Domain\DTOs;

class InteractionLogDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $experimentId,
        public readonly string $userId,
        public readonly string $uiVersion,
        public readonly string $eventType,
        public readonly ?array $eventData,
        public readonly ?string $targetElement,
        public readonly ?string $pageUrl,
        public readonly ?int $viewportWidth,
        public readonly ?int $viewportHeight,
        public readonly ?string $browser,
        public readonly ?string $os,
        public readonly ?float $taskTime,
        public readonly ?float $sessionTime,
        public readonly ?float $idleTime,
        public readonly ?string $timestamp,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            experimentId: $data['experiment_id'],
            userId: $data['user_id'],
            uiVersion: $data['ui_version'],
            eventType: $data['event_type'],
            eventData: $data['event_data'] ?? null,
            targetElement: $data['target_element'] ?? null,
            pageUrl: $data['page_url'] ?? null,
            viewportWidth: $data['viewport_width'] ?? null,
            viewportHeight: $data['viewport_height'] ?? null,
            browser: $data['browser'] ?? null,
            os: $data['os'] ?? null,
            taskTime: $data['task_time'] ?? null,
            sessionTime: $data['session_time'] ?? null,
            idleTime: $data['idle_time'] ?? null,
            timestamp: $data['timestamp'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'experiment_id' => $this->experimentId,
            'user_id' => $this->userId,
            'ui_version' => $this->uiVersion,
            'event_type' => $this->eventType,
            'event_data' => $this->eventData,
            'target_element' => $this->targetElement,
            'page_url' => $this->pageUrl,
            'viewport_width' => $this->viewportWidth,
            'viewport_height' => $this->viewportHeight,
            'browser' => $this->browser,
            'os' => $this->os,
            'task_time' => $this->taskTime,
            'session_time' => $this->sessionTime,
            'idle_time' => $this->idleTime,
            'timestamp' => $this->timestamp,
        ];
    }
}
