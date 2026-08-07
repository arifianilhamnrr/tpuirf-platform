<?php

namespace App\Domain\Repositories;

interface UiTemplateRepositoryInterface
{
    public function findAll(): array;
    public function findById(string $id): ?array;
    public function findByVersion(string $version): ?array;
    public function create(array $data): array;
    public function update(string $id, array $data): array;
    public function delete(string $id): bool;
    public function count(): int;
}
