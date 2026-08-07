<?php

namespace App\Domain\DTOs;

class RecommendationDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $userId,
        public readonly ?string $experimentId,
        public readonly string $recommendedUi,
        public readonly float $confidenceScore,
        public readonly ?array $shapValues,
        public readonly ?array $features,
        public readonly ?string $modelVersion,
        public readonly ?string $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            userId: $data['user_id'],
            experimentId: $data['experiment_id'] ?? null,
            recommendedUi: $data['recommended_ui'],
            confidenceScore: $data['confidence_score'],
            shapValues: $data['shap_values'] ?? null,
            features: $data['features'] ?? null,
            modelVersion: $data['model_version'] ?? null,
            createdAt: $data['created_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'experiment_id' => $this->experimentId,
            'recommended_ui' => $this->recommendedUi,
            'confidence_score' => $this->confidenceScore,
            'shap_values' => $this->shapValues,
            'features' => $this->features,
            'model_version' => $this->modelVersion,
            'created_at' => $this->createdAt,
        ];
    }
}
