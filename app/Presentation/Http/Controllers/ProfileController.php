<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Interfaces\AuthServiceInterface;
use App\Application\Interfaces\UserProfileServiceInterface;
use App\Presentation\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UserProfileServiceInterface $profileService,
        private readonly AuthServiceInterface $authService,
    ) {}

    public function show(): View
    {
        $user = $this->authService->getCurrentUser();
        $profile = $this->profileService->getProfile($user->id);
        $profileComplete = $this->profileService->isProfileComplete($user->id);
        $editing = ! $profileComplete;

        return view('profile.index', compact('user', 'profile', 'editing', 'profileComplete'));
    }

    public function edit(): View
    {
        $user = $this->authService->getCurrentUser();
        $profile = $this->profileService->getProfile($user->id);
        $profileComplete = $this->profileService->isProfileComplete($user->id);
        $editing = true;

        return view('profile.index', compact('user', 'profile', 'editing', 'profileComplete'));
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $this->authService->getCurrentUser();
        $wasComplete = $this->profileService->isProfileComplete($user->id);
        $profileData = $request->validated();
        $profileData['age'] = Carbon::parse($user->dateOfBirth)->age;
        $this->profileService->createOrUpdateProfile($user->id, $profileData);

        if ($wasComplete) {
            return redirect()->route('respondent.profile.show')->with('success', 'Profil berhasil diperbarui!');
        }

        return redirect()->route('respondent.dashboard')->with('success', 'Profil berhasil dilengkapi!');
    }
}
