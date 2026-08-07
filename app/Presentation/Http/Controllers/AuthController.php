<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\AuthServiceInterface;
use App\Presentation\Http\Requests\LoginRequest;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
    ) {}

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $user = $this->authService->login(
            $request->input('name'),
            $request->input('date_of_birth')
        );

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'researcher' => redirect()->route('researcher.dashboard'),
            default => redirect()->route('respondent.dashboard'),
        };
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout();
        return redirect()->route('login');
    }
}

