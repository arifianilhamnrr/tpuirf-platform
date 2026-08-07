<?php

namespace App\Application\Services;

use App\Application\Interfaces\AuthServiceInterface;
use App\Domain\DTOs\UserDTO;
use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function login(string $name, string $dateOfBirth): UserDTO
    {
        $user = $this->userRepository->findByNameAndDob($name, $dateOfBirth);

        if (! $user) {
            // Auto-create as respondent
            $email = strtolower(str_replace(' ', '.', $name)).'.'.str_replace('-', '', $dateOfBirth).'@respondent.tpuirf';
            $user = $this->userRepository->create(UserDTO::fromArray([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($dateOfBirth),
                'role' => 'respondent',
                'date_of_birth' => $dateOfBirth,
                'is_active' => true,
            ]));
        }

        if (! $user->isActive) {
            throw ValidationException::withMessages([
                'name' => 'Akun ini sedang dinonaktifkan.',
            ]);
        }

        Session::regenerate();

        Session::put('user_id', $user->id);
        Session::put('user_role', $user->role);
        Session::put('user_name', $user->name);

        return $user;
    }

    public function logout(): void
    {
        Session::invalidate();
        Session::regenerateToken();
    }

    public function getCurrentUser(): ?UserDTO
    {
        $userId = Session::get('user_id');
        if (! $userId) {
            return null;
        }

        return $this->userRepository->findById($userId);
    }
}
