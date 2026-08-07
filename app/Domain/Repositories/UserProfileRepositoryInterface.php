<?php

namespace App\Domain\Repositories;

use App\Domain\DTOs\UserProfileDTO;

interface UserProfileRepositoryInterface
{
    public function findByUserId(string $userId): ?UserProfileDTO;
    public function create(UserProfileDTO $profile): UserProfileDTO;
    public function update(string $id, UserProfileDTO $profile): UserProfileDTO;
    public function delete(string $id): bool;
    public function findAll(): array;
}
