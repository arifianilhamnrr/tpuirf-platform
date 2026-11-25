<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('survey-start', fn (Request $request) => Limit::perMinute(10)
            ->by($this->respondentKey($request, 'start')));

        RateLimiter::for('survey-selection', fn (Request $request) => Limit::perMinute(60)
            ->by($this->respondentKey($request, 'selection')));

        RateLimiter::for('survey-events', fn (Request $request) => Limit::perMinute(120)
            ->by($this->respondentKey($request, 'events')));

        RateLimiter::for('survey-xai-evaluation', fn (Request $request) => Limit::perMinute(10)
            ->by($this->respondentKey($request, 'xai-evaluation')));
    }

    private function respondentKey(Request $request, string $scope): string
    {
        $userId = (string) $request->session()->get('user_id', 'guest');
        $experimentId = (string) $request->route('experimentId', 'none');

        return implode(':', [$scope, $userId, $experimentId]);
    }
}
