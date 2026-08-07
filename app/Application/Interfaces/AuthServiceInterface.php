<?php

namespace App\Application\Interfaces;

use App\Domain\DTOs\UserDTO;

interface AuthServiceInterface
{
    public function login(string $name, string $dateOfBirth): UserDTO;
    public function logout(): void;
    public function getCurrentUser(): ?UserDTO;
}
