<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\RecommendationServiceInterface;
use App\Infrastructure\Persistence\Models\AuditLog;
use App\Infrastructure\Persistence\Models\Configuration;
use App\Infrastructure\Persistence\Models\Experiment;
use App\Infrastructure\Persistence\Models\UiTemplate;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        private readonly RecommendationServiceInterface $recommendationService,
    ) {}

    public function manageResearchers(Request $request): View
    {
        $researchers = User::query()
            ->where('role', 'researcher')
            ->when($request->string('search')->trim()->isNotEmpty(), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->trim().'%';
                $query->where(fn ($inner) => $inner->where('name', 'like', $search)->orWhere('email', 'like', $search));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.researchers', compact('researchers'));
    }

    public function createResearcher(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'date_of_birth' => ['required', 'date', 'before:today'],
        ]);

        $researcher = User::create([
            ...$validated,
            'password' => Hash::make($validated['date_of_birth']),
            'role' => 'researcher',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->audit($request, 'created', $researcher, null, $researcher->only('name', 'email', 'date_of_birth', 'role', 'is_active'));

        return redirect()->route('admin.researchers')->with('success', 'Akun peneliti berhasil dibuat. Peneliti dapat masuk memakai nama dan tanggal lahir.');
    }

    public function manageRespondents(Request $request): View
    {
        $respondents = User::query()
            ->where('role', 'respondent')
            ->withCount(['experiments', 'recommendations'])
            ->when($request->string('search')->trim()->isNotEmpty(), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->trim().'%';
                $query->where(fn ($inner) => $inner->where('name', 'like', $search)->orWhere('email', 'like', $search));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->value() === 'active'))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.respondents', compact('respondents'));
    }

    public function toggleUserStatus(Request $request, User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['researcher', 'respondent'], true), 404);

        $old = ['is_active' => $user->is_active];
        $user->update(['is_active' => ! $user->is_active]);
        $this->audit($request, $user->is_active ? 'activated' : 'deactivated', $user, $old, ['is_active' => $user->is_active]);

        return back()->with('success', "Akun {$user->name} berhasil ".($user->is_active ? 'diaktifkan.' : 'dinonaktifkan.'));
    }

    public function manageExperiments(Request $request): View
    {
        $experiments = Experiment::query()
            ->with('user:id,name,email')
            ->withCount(['tasks', 'interactionEvents'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->value()))
            ->when($request->string('search')->trim()->isNotEmpty(), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->trim().'%';
                $query->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', $search)->orWhere('email', 'like', $search));
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();
        $statusCounts = Experiment::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.experiments', compact('experiments', 'statusCounts'));
    }

    public function manageTemplates(): View
    {
        $templates = UiTemplate::query()->orderBy('version')->get();

        return view('admin.templates', compact('templates'));
    }

    public function updateTemplate(Request $request, UiTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'config' => ['required', 'json'],
        ]);
        $config = json_decode($validated['config'], true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($config)) {
            throw ValidationException::withMessages(['config' => 'Konfigurasi harus berupa objek JSON.']);
        }

        $old = $template->only('name', 'description', 'config');
        $template->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'config' => $config,
        ]);
        $this->audit($request, 'updated', $template, $old, $template->only('name', 'description', 'config'));

        return back()->with('success', "Template {$template->version} berhasil diperbarui.");
    }

    public function toggleTemplate(Request $request, UiTemplate $template): RedirectResponse
    {
        $old = ['is_active' => $template->is_active];
        $template->update(['is_active' => ! $template->is_active]);
        $this->audit($request, $template->is_active ? 'activated' : 'deactivated', $template, $old, ['is_active' => $template->is_active]);

        return back()->with('success', "Template {$template->version} berhasil ".($template->is_active ? 'diaktifkan.' : 'dinonaktifkan.'));
    }

    public function manageLogs(Request $request): View
    {
        $logs = AuditLog::query()
            ->with('user:id,name')
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')->value()))
            ->when($request->filled('entity'), fn ($query) => $query->where('entity_type', $request->string('entity')->value()))
            ->orderByDesc('timestamp')
            ->paginate(50)
            ->withQueryString();
        $actions = AuditLog::query()->distinct()->orderBy('action')->pluck('action');
        $entities = AuditLog::query()->distinct()->orderBy('entity_type')->pluck('entity_type');

        return view('admin.logs', compact('logs', 'actions', 'entities'));
    }

    public function configuration(): View
    {
        $configs = Configuration::query()->orderBy('group')->orderBy('key')->get();

        return view('admin.configuration', compact('configs'));
    }

    public function createConfiguration(Request $request): RedirectResponse
    {
        $validated = $this->validateConfiguration($request, null);
        $configuration = Configuration::create($validated);
        $this->audit($request, 'created', $configuration, null, $configuration->only('key', 'value', 'description', 'group'));

        return back()->with('success', 'Konfigurasi berhasil ditambahkan.');
    }

    public function updateConfiguration(Request $request, Configuration $configuration): RedirectResponse
    {
        $validated = $this->validateConfiguration($request, $configuration);
        $old = $configuration->only('key', 'value', 'description', 'group');
        $configuration->update($validated);
        $this->audit($request, 'updated', $configuration, $old, $configuration->only('key', 'value', 'description', 'group'));

        return back()->with('success', 'Konfigurasi berhasil diperbarui.');
    }

    public function trainModel(Request $request): RedirectResponse
    {
        $trained = $this->recommendationService->trainModel();
        $this->audit($request, $trained ? 'trained' : 'training_failed', 'ml_model', null, null, [
            'model_status' => $this->recommendationService->getModelStatus(),
        ]);

        return redirect()->route('admin.dashboard')->with(
            $trained ? 'success' : 'error',
            $trained ? 'Model multi-target XGBoost berhasil dilatih.' : 'Training gagal. Model aktif sebelumnya tetap digunakan; pastikan service ML aktif dan setiap kelas target memiliki cukup data.',
        );
    }

    private function validateConfiguration(Request $request, ?Configuration $configuration): array
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_.-]+$/', Rule::unique('configurations', 'key')->ignore($configuration?->id)],
            'value' => ['required', 'json'],
            'description' => ['nullable', 'string', 'max:1000'],
            'group' => ['required', 'string', 'max:50'],
        ]);
        $value = json_decode($validated['value'], true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($value)) {
            throw ValidationException::withMessages(['value' => 'Nilai harus berupa objek atau array JSON.']);
        }
        $validated['value'] = $value;

        return $validated;
    }

    private function audit(Request $request, string $action, User|UiTemplate|Configuration|string $entity, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::create([
            'user_id' => session('user_id'),
            'action' => $action,
            'entity_type' => is_string($entity) ? $entity : class_basename($entity),
            'entity_id' => is_string($entity) ? null : $entity->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'timestamp' => now(),
        ]);
    }
}
