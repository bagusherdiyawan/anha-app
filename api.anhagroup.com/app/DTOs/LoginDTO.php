<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class LoginDTO
{
    public function __construct(
        public string $identifier,
        public string $password,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            identifier: $request->identifier,
            password: $request->password,
        );
    }
}
