<?php

namespace App\Application\Interfaces;

use App\Domain\DTOs\UserProfileDTO;

interface UserProfileServiceInterface
{
    public function getProfile(string $userId): ?UserProfileDTO;
    public function createOrUpdateProfile(string $userId, array $data): UserProfileDTO;
    public function isProfileComplete(string $userId): bool;
}
