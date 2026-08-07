<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\UiTemplateRepositoryInterface;
use App\Infrastructure\Persistence\Models\UiTemplate;

class UiTemplateRepository implements UiTemplateRepositoryInterface
{
    public function findAll(): array
    {
        return UiTemplate::where('is_active', true)
            ->orderBy('version')
            ->get()
            ->toArray();
    }

    public function findById(string $id): ?array
    {
        $template = UiTemplate::find($id);
        return $template ? $template->toArray() : null;
    }

    public function findByVersion(string $version): ?array
    {
        $template = UiTemplate::where('version', $version)->first();
        return $template ? $template->toArray() : null;
    }

    public function create(array $data): array
    {
        return UiTemplate::create($data)->toArray();
    }

    public function update(string $id, array $data): array
    {
        $template = UiTemplate::findOrFail($id);
        $template->update($data);
        return $template->fresh()->toArray();
    }

    public function delete(string $id): bool
    {
        return UiTemplate::findOrFail($id)->delete();
    }

    public function count(): int
    {
        return UiTemplate::count();
    }
}
