<?php

namespace App\Domain\DTOs;

class DatasetExportDTO
{
    public function __construct(
        public readonly array $headers,
        public readonly array $rows,
        public readonly string $type,
        public readonly ?array $metadata,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            headers: $data['headers'],
            rows: $data['rows'],
            type: $data['type'] ?? 'training',
            metadata: $data['metadata'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'headers' => $this->headers,
            'rows' => $this->rows,
            'type' => $this->type,
            'metadata' => $this->metadata,
        ];
    }
}
