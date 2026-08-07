<?php

namespace App\Domain\DTOs;

class UserProfileDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $userId,
        public readonly ?int $age,
        public readonly ?string $gender,
        public readonly ?string $education,
        public readonly ?string $occupation,
        public readonly ?int $digitalLiteracy,
        public readonly ?string $deviceType,
        public readonly ?string $visualPreference,
        public readonly ?string $contextEnvironment,
        public readonly ?int $aiFamiliarity,
        public readonly ?array $additionalData,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            userId: $data['user_id'],
            age: $data['age'] ?? null,
            gender: $data['gender'] ?? null,
            education: $data['education'] ?? null,
            occupation: $data['occupation'] ?? null,
            digitalLiteracy: $data['digital_literacy'] ?? null,
            deviceType: $data['device_type'] ?? null,
            visualPreference: $data['visual_preference'] ?? null,
            contextEnvironment: $data['context_environment'] ?? null,
            aiFamiliarity: $data['ai_familiarity'] ?? null,
            additionalData: $data['additional_data'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'age' => $this->age,
            'gender' => $this->gender,
            'education' => $this->education,
            'occupation' => $this->occupation,
            'digital_literacy' => $this->digitalLiteracy,
            'device_type' => $this->deviceType,
            'visual_preference' => $this->visualPreference,
            'context_environment' => $this->contextEnvironment,
            'ai_familiarity' => $this->aiFamiliarity,
            'additional_data' => $this->additionalData,
        ];
    }
}
