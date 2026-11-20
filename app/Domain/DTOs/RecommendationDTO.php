<?php

namespace App\Domain\DTOs;

class RecommendationDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $userId,
        public readonly ?string $experimentId,
        public readonly string $recommendedUi,
        public readonly ?array $recommendedConfig,
        public readonly float $confidenceScore,
        public readonly ?array $confidenceByTarget,
        public readonly ?array $shapValues,
        public readonly ?array $features,
        public readonly ?array $explanations,
        public readonly ?array $modelOutputs,
        public readonly ?array $inputFeatures,
        public readonly ?string $modelVersion,
        public readonly ?string $algorithm,
        public readonly ?string $predictedAt,
        public readonly ?string $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            userId: $data['user_id'],
            experimentId: $data['experiment_id'] ?? null,
            recommendedUi: $data['recommended_ui'],
            recommendedConfig: $data['recommended_config'] ?? null,
            confidenceScore: $data['confidence_score'],
            confidenceByTarget: $data['confidence_by_target'] ?? null,
            shapValues: $data['shap_values'] ?? null,
            features: $data['features'] ?? null,
            explanations: $data['explanations'] ?? null,
            modelOutputs: $data['model_outputs'] ?? null,
            inputFeatures: $data['input_features'] ?? null,
            modelVersion: $data['model_version'] ?? null,
            algorithm: $data['algorithm'] ?? null,
            predictedAt: isset($data['predicted_at']) ? (string) $data['predicted_at'] : null,
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
            'recommended_config' => $this->recommendedConfig,
            'confidence_score' => $this->confidenceScore,
            'confidence_by_target' => $this->confidenceByTarget,
            'shap_values' => $this->shapValues,
            'features' => $this->features,
            'explanations' => $this->explanations,
            'model_outputs' => $this->modelOutputs,
            'input_features' => $this->inputFeatures,
            'model_version' => $this->modelVersion,
            'algorithm' => $this->algorithm,
            'predicted_at' => $this->predictedAt,
            'created_at' => $this->createdAt,
        ];
    }
}
