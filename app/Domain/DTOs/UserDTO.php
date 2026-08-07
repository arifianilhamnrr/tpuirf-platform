<?php

namespace App\Domain\DTOs;

class UserDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $password,
        public readonly string $role,
        public readonly ?string $dateOfBirth,
        public readonly ?bool $isActive,
        public readonly ?string $emailVerifiedAt,
        public readonly ?string $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            email: $data['email'],
            password: $data['password'] ?? null,
            role: $data['role'] ?? 'respondent',
            dateOfBirth: $data['date_of_birth'] ?? null,
            isActive: $data['is_active'] ?? true,
            emailVerifiedAt: $data['email_verified_at'] ?? null,
            createdAt: $data['created_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
            'date_of_birth' => $this->dateOfBirth,
            'is_active' => $this->isActive,
            'email_verified_at' => $this->emailVerifiedAt,
            'created_at' => $this->createdAt,
        ];
    }
}
