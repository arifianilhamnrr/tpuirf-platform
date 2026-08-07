<?php

namespace App\Infrastructure\Providers;

use App\Application\Interfaces\AnalyticsServiceInterface;
use App\Application\Interfaces\AuthServiceInterface;
use App\Application\Interfaces\DatasetExportServiceInterface;
use App\Application\Interfaces\ExperimentServiceInterface;
use App\Application\Interfaces\ExplainabilityServiceInterface;
use App\Application\Interfaces\InteractionLoggerServiceInterface;
use App\Application\Interfaces\PreferenceExperimentServiceInterface;
use App\Application\Interfaces\RecommendationServiceInterface;
use App\Application\Interfaces\UserProfileServiceInterface;
use App\Application\Services\AnalyticsService;
use App\Application\Services\AuthService;
use App\Application\Services\DatasetExportService;
use App\Application\Services\ExperimentService;
use App\Application\Services\ExplainabilityService;
use App\Application\Services\InteractionLoggerService;
use App\Application\Services\PreferenceExperimentService;
use App\Application\Services\RecommendationService;
use App\Application\Services\UserProfileService;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(UserProfileServiceInterface::class, UserProfileService::class);
        $this->app->bind(ExperimentServiceInterface::class, ExperimentService::class);
        $this->app->bind(InteractionLoggerServiceInterface::class, InteractionLoggerService::class);
        $this->app->bind(RecommendationServiceInterface::class, RecommendationService::class);
        $this->app->bind(ExplainabilityServiceInterface::class, ExplainabilityService::class);
        $this->app->bind(AnalyticsServiceInterface::class, AnalyticsService::class);
        $this->app->bind(DatasetExportServiceInterface::class, DatasetExportService::class);
        $this->app->bind(PreferenceExperimentServiceInterface::class, PreferenceExperimentService::class);
    }
}
