<?php

namespace App\Presentation\Http\Middleware;

use App\Application\Interfaces\UserProfileServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileCompleteMiddleware
{
    public function __construct(
        private readonly UserProfileServiceInterface $profileService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $userId = (string) $request->session()->get('user_id');

        if ($userId !== '' && $this->profileService->isProfileComplete($userId)) {
            return $next($request);
        }

        $message = 'Lengkapi profil terlebih dahulu sebelum memulai eksperimen.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect_url' => route('respondent.profile.show'),
            ], 409);
        }

        return redirect()
            ->route('respondent.profile.show')
            ->with('error', $message);
    }
}
