<?php

namespace Modules\User;

use App\Models\User;

readonly class UserDTO
{
    public function __construct(
        public int $id,
        public string $email,
        public string $name,
    ) {}

    public static function fromEloquentModel(User $user): self
    {
        return new self(
            id: $user->id,
            email: $user->email,
            name: $user->name,
        );
    }
}
