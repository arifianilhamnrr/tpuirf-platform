<?php

namespace App\Application\Services;

use App\Application\Interfaces\UserProfileServiceInterface;
use App\Domain\DTOs\UserProfileDTO;
use App\Domain\Repositories\UserProfileRepositoryInterface;

class UserProfileService implements UserProfileServiceInterface
{
    public function __construct(
        private readonly UserProfileRepositoryInterface $profileRepository,
    ) {}

    public function getProfile(string $userId): ?UserProfileDTO
    {
        return $this->profileRepository->findByUserId($userId);
    }

    public function createOrUpdateProfile(string $userId, array $data): UserProfileDTO
    {
        $data['user_id'] = $userId;
        
        $existing = $this->profileRepository->findByUserId($userId);
        
        if ($existing) {
            return $this->profileRepository->update($existing->id, UserProfileDTO::fromArray($data));
        }
        
        return $this->profileRepository->create(UserProfileDTO::fromArray($data));
    }

    public function isProfileComplete(string $userId): bool
    {
        $profile = $this->profileRepository->findByUserId($userId);
        
        if (!$profile) {
            return false;
        }
        
        return ! is_null($profile->age)
            && ! is_null($profile->gender)
            && ! is_null($profile->education)
            && ! is_null($profile->digitalLiteracy)
            && ! is_null($profile->deviceType)
            && ! is_null($profile->visualPreference)
            && ! is_null($profile->contextEnvironment)
            && ! is_null($profile->aiFamiliarity);
    }
}
