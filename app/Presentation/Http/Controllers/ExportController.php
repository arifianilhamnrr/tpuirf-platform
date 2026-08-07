<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\DatasetExportServiceInterface;
use Illuminate\Routing\Controller;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    public function __construct(
        private readonly DatasetExportServiceInterface $exportService,
    ) {}

    public function csv(string $type = 'full'): Response
    {
        $filepath = $this->exportService->exportCsv($type);
        return response()->download($filepath)->deleteFileAfterSend();
    }

    public function excel(string $type = 'full'): Response
    {
        $filepath = $this->exportService->exportExcel($type);
        return response()->download($filepath)->deleteFileAfterSend();
    }

    public function json(string $type = 'full'): Response
    {
        $filepath = $this->exportService->exportJson($type);
        return response()->download($filepath)->deleteFileAfterSend();
    }
}
