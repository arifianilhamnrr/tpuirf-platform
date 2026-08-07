<?php

namespace App\Infrastructure\Repositories;

use App\Domain\DTOs\UserProfileDTO;
use App\Domain\Repositories\UserProfileRepositoryInterface;
use App\Infrastructure\Persistence\Models\UserProfile;

class UserProfileRepository implements UserProfileRepositoryInterface
{
    public function findByUserId(string $userId): ?UserProfileDTO
    {
        $profile = UserProfile::where('user_id', $userId)->first();
        return $profile ? $this->toDTO($profile) : null;
    }

    public function create(UserProfileDTO $profile): UserProfileDTO
    {
        $model = UserProfile::create($profile->toArray());
        return $this->toDTO($model);
    }

    public function update(string $id, UserProfileDTO $profile): UserProfileDTO
    {
        $model = UserProfile::findOrFail($id);
        $model->update($profile->toArray());
        return $this->toDTO($model->fresh());
    }

    public function delete(string $id): bool
    {
        return UserProfile::findOrFail($id)->delete();
    }

    public function findAll(): array
    {
        return UserProfile::all()->map(fn(UserProfile $p) => $this->toDTO($p))->toArray();
    }

    private function toDTO(UserProfile $profile): UserProfileDTO
    {
        return UserProfileDTO::fromArray($profile->toArray());
    }
}
