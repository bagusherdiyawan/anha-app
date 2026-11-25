<?php

namespace App\Repositories;

use App\Models\User;
use Carbon\Carbon;

class AuthRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByPhone(string $phone): ?User
    {
        return User::where('phone', $phone)->first();
    }

    public function createToken(User $user): string
    {
        $expiration = Carbon::now()->addDays(30);

        return $user
            ->createToken('auth_token', ['*'], $expiration)
            ->plainTextToken;
    }

    public function create(array $data): User
    {
        return User::create($data);
    }
}
