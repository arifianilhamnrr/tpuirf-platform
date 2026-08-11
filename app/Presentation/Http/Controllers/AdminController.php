<?php

namespace App\Presentation\Http\Controllers;
use App\Application\Interfaces\RecommendationServiceInterface;

use App\Domain\Repositories\ExperimentRepositoryInterface;
use App\Domain\Repositories\InteractionLogRepositoryInterface;
use App\Domain\Repositories\UiTemplateRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Models\AuditLog;
use App\Infrastructure\Persistence\Models\Configuration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly ExperimentRepositoryInterface $experimentRepository,
        private readonly UiTemplateRepositoryInterface $uiTemplateRepository,
        private readonly InteractionLogRepositoryInterface $logRepository,
        private readonly RecommendationServiceInterface $recommendationService,
    ) {}

    public function manageResearchers(): View
    {
        $researchers = $this->userRepository->findAll(['role' => 'researcher']);

        return view('dashboard.admin', compact('researchers'));
    }

    public function createResearcher(Request $request): RedirectResponse
    {
        return redirect()->back()->with('success', 'Akun peneliti berhasil dibuat.');
    }

    public function manageRespondents(): View
    {
        $respondents = $this->userRepository->findAll(['role' => 'respondent']);

        return view('dashboard.admin', compact('respondents'));
    }

    public function manageExperiments(): View
    {
        $experiments = $this->experimentRepository->findAll();

        return view('dashboard.admin', compact('experiments'));
    }

    public function manageTemplates(): View
    {
        $templates = $this->uiTemplateRepository->findAll();

        return view('dashboard.admin', compact('templates'));
    }

    public function manageLogs(): View
    {
        $logs = AuditLog::orderBy('timestamp', 'desc')->paginate(50);

        return view('dashboard.admin', compact('logs'));
    }

    public function configuration(): View
    {
        $configs = Configuration::all();

        return view('dashboard.admin', compact('configs'));
    }

    public function updateConfiguration(Request $request): RedirectResponse
    {
        return redirect()->back()->with('success', 'Konfigurasi berhasil diperbarui.');
    }

    public function trainModel(): RedirectResponse
    {
        $trained = $this->recommendationService->trainModel();

        return redirect()->route('admin.dashboard')->with(
            $trained ? 'success' : 'error',
            $trained ? 'Model Random Forest dan XGBoost berhasil dilatih.' : 'Training gagal. Pastikan service Python dan dataset tersedia.',
        );
    }
}
