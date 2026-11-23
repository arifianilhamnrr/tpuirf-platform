<?php

use App\Presentation\Http\Controllers\AdminController;
use App\Presentation\Http\Controllers\AnalyticsController;
use App\Presentation\Http\Controllers\AuthController;
use App\Presentation\Http\Controllers\DashboardController;
use App\Presentation\Http\Controllers\ExperimentController;
use App\Presentation\Http\Controllers\ExportController;
use App\Presentation\Http\Controllers\PreferenceExperimentController;
use App\Presentation\Http\Controllers\PreferenceExperimentPageController;
use App\Presentation\Http\Controllers\ProfileController;
use App\Presentation\Http\Controllers\RecommendationController;
use App\Presentation\Http\Middleware\RespondentMiddleware;
use App\Presentation\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth.session'])->group(function () {

    Route::middleware([RespondentMiddleware::class])->prefix('respondent')->name('respondent.')->group(function () {
        Route::middleware('profile.complete')->prefix('preference-experiment')->name('preference-experiment.')->group(function () {
            Route::get('/', [PreferenceExperimentPageController::class, 'introduction'])->name('introduction');
            Route::post('/start', [PreferenceExperimentController::class, 'start'])->middleware('throttle:10,1')->name('start');
            Route::get('/{experimentId}/tasks/{taskKey}/page', [PreferenceExperimentPageController::class, 'task'])->name('tasks.page');
            Route::get('/{experimentId}/tasks/{taskKey}', [PreferenceExperimentController::class, 'task'])->name('tasks.show');
            Route::post('/{experimentId}/tasks/{taskKey}/selection', [PreferenceExperimentController::class, 'select'])->middleware('throttle:60,1')->name('tasks.select');
            Route::post('/{experimentId}/events/batch', [PreferenceExperimentController::class, 'events'])->middleware('throttle:120,1')->name('events.batch');
            Route::get('/{experimentId}/evaluation', [PreferenceExperimentPageController::class, 'evaluation'])->name('evaluation.page');
            Route::post('/{experimentId}/evaluation', [PreferenceExperimentController::class, 'evaluate'])->name('evaluation');
            Route::get('/{experimentId}/complete', [PreferenceExperimentPageController::class, 'complete'])->name('complete.page');
            Route::post('/{experimentId}/complete', [PreferenceExperimentController::class, 'complete'])->name('complete');
        });

        Route::get('/dashboard', [DashboardController::class, 'respondent'])->name('dashboard');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::middleware('profile.complete')->group(function () {
            Route::get('/experiment/start', [ExperimentController::class, 'start'])->name('experiment.start');
            Route::get('/experiment/run', [ExperimentController::class, 'run'])->name('experiment.run');
            Route::post('/experiment/complete-step', [ExperimentController::class, 'completeStep'])->name('experiment.complete-step');
            Route::get('/experiment/preference', [ExperimentController::class, 'preference'])->name('experiment.preference');
            Route::post('/experiment/preference', [ExperimentController::class, 'submitPreference'])->name('experiment.submit-preference');
            Route::get('/experiment/evaluation', [ExperimentController::class, 'evaluation'])->name('experiment.evaluation');
            Route::post('/experiment/evaluation', [ExperimentController::class, 'submitEvaluation'])->name('experiment.submit-evaluation');
            Route::get('/experiment/complete', [ExperimentController::class, 'complete'])->name('experiment.complete');
            Route::get('/recommendation', [RecommendationController::class, 'show'])->name('recommendation.show');
            Route::post('/recommendation/generate', [RecommendationController::class, 'generate'])->name('recommendation.generate');
            Route::post('/recommendation/xai-evaluation', [RecommendationController::class, 'storeXaiEvaluation'])
                ->middleware('throttle:10,1')
                ->name('recommendation.xai-evaluation.store');
        });
    });

    Route::middleware([RoleMiddleware::class.':researcher'])->prefix('researcher')->name('researcher.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'researcher'])->name('dashboard');
    });

    Route::middleware([RoleMiddleware::class.':admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        Route::get('/researchers', [AdminController::class, 'manageResearchers'])->name('researchers');
        Route::post('/researchers', [AdminController::class, 'createResearcher'])->name('researchers.store');
        Route::get('/respondents', [AdminController::class, 'manageRespondents'])->name('respondents');
        Route::patch('/users/{user}/status', [AdminController::class, 'toggleUserStatus'])->name('users.status');
        Route::get('/experiments', [AdminController::class, 'manageExperiments'])->name('experiments');
        Route::get('/templates', [AdminController::class, 'manageTemplates'])->name('templates');
        Route::put('/templates/{template}', [AdminController::class, 'updateTemplate'])->name('templates.update');
        Route::patch('/templates/{template}/status', [AdminController::class, 'toggleTemplate'])->name('templates.status');
        Route::get('/logs', [AdminController::class, 'manageLogs'])->name('logs');
        Route::get('/configuration', [AdminController::class, 'configuration'])->name('configuration');
        Route::post('/configuration', [AdminController::class, 'createConfiguration'])->name('configuration.store');
        Route::put('/configuration/{configuration}', [AdminController::class, 'updateConfiguration'])->name('configuration.update');
        Route::post('/model/train', [AdminController::class, 'trainModel'])->name('model.train');
    });

    Route::middleware([RoleMiddleware::class.':researcher,admin'])->group(function () {
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/export/csv/{type?}', [ExportController::class, 'csv'])->name('export.csv');
        Route::get('/export/excel/{type?}', [ExportController::class, 'excel'])->name('export.excel');
        Route::get('/export/json/{type?}', [ExportController::class, 'json'])->name('export.json');
    });
});

Route::post('/interaction/log', fn () => response()->json([
    'message' => 'Endpoint interaksi lama sudah dinonaktifkan. Gunakan endpoint batch yang terautentikasi.',
], 410))->name('interaction.log');
