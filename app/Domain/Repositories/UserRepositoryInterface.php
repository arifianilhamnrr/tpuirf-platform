<?php

namespace App\Domain\Repositories;

use App\Domain\DTOs\UserDTO;

interface UserRepositoryInterface
{
    public function findAll(array $filters = []): array;

    public function findById(string $id): ?UserDTO;

    public function findByEmail(string $email): ?UserDTO;

    public function findByNameAndDob(string $name, string $dateOfBirth): ?UserDTO;

    public function create(UserDTO $user): UserDTO;

    public function update(string $id, UserDTO $user): UserDTO;

    public function delete(string $id): bool;

    public function count(array $filters = []): int;
}
