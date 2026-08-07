<?php

namespace App\Infrastructure\Repositories;

use App\Domain\DTOs\UserDTO;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function findAll(array $filters = []): array
    {
        $query = User::query();

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->get()->map(fn (User $user) => $this->toDTO($user))->toArray();
    }

    public function findById(string $id): ?UserDTO
    {
        $user = User::find($id);

        return $user ? $this->toDTO($user) : null;
    }

    public function findByEmail(string $email): ?UserDTO
    {
        $user = User::where('email', $email)->first();

        return $user ? $this->toDTO($user) : null;
    }

    public function findByNameAndDob(string $name, string $dateOfBirth): ?UserDTO
    {
        $user = User::where('name', $name)
            ->whereDate('date_of_birth', $dateOfBirth)
            ->first();

        return $user ? $this->toDTO($user) : null;
    }

    public function create(UserDTO $user): UserDTO
    {
        $model = User::create($user->toArray());

        return $this->toDTO($model);
    }

    public function update(string $id, UserDTO $user): UserDTO
    {
        $model = User::findOrFail($id);
        $model->update($user->toArray());

        return $this->toDTO($model->fresh());
    }

    public function delete(string $id): bool
    {
        return User::findOrFail($id)->delete();
    }

    public function count(array $filters = []): int
    {
        $query = User::query();

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->count();
    }

    private function toDTO(User $user): UserDTO
    {
        return UserDTO::fromArray($user->toArray());
    }
}
