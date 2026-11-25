<?php

namespace App\Services;

use App\DTOs\LoginDTO;
use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected AuthRepository $authRepository
    ) {}

    public function login(LoginDTO $dto): array
    {
        $user = $this->authRepository->findByEmail($dto->identifier)
            ?? $this->authRepository->findByPhone($dto->identifier);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => ['Kredensial tidak valid.']
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'identifier' => ['Akun tidak aktif.']
            ]);
        }

        if (!in_array($user->type, ['b2c'])) {
            throw ValidationException::withMessages([
                'identifier' => ['Gunakan halaman login yang sesuai.']
            ]);
        }

        $token = $this->authRepository->createToken($user);

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }
}
