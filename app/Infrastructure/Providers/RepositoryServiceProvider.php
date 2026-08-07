<?php

namespace App\Infrastructure\Providers;

use App\Domain\Repositories\ExperimentRepositoryInterface;
use App\Domain\Repositories\InteractionLogRepositoryInterface;
use App\Domain\Repositories\RecommendationRepositoryInterface;
use App\Domain\Repositories\TrustEvaluationRepositoryInterface;
use App\Domain\Repositories\UiTemplateRepositoryInterface;
use App\Domain\Repositories\UserProfileRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Repositories\ExperimentRepository;
use App\Infrastructure\Repositories\InteractionLogRepository;
use App\Infrastructure\Repositories\RecommendationRepository;
use App\Infrastructure\Repositories\TrustEvaluationRepository;
use App\Infrastructure\Repositories\UiTemplateRepository;
use App\Infrastructure\Repositories\UserProfileRepository;
use App\Infrastructure\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserProfileRepositoryInterface::class, UserProfileRepository::class);
        $this->app->bind(ExperimentRepositoryInterface::class, ExperimentRepository::class);
        $this->app->bind(UiTemplateRepositoryInterface::class, UiTemplateRepository::class);
        $this->app->bind(InteractionLogRepositoryInterface::class, InteractionLogRepository::class);
        $this->app->bind(RecommendationRepositoryInterface::class, RecommendationRepository::class);
        $this->app->bind(TrustEvaluationRepositoryInterface::class, TrustEvaluationRepository::class);
    }
}
