<?php

namespace App\Application\Interfaces;

interface DatasetExportServiceInterface
{
    public function exportCsv(string $type): string;

    public function exportExcel(string $type): string;

    public function exportJson(string $type): string;

    public function getTrainingDataset(): array;

    public function getTestingDataset(): array;

    public function getFullDataset(): array;
}
