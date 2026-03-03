<?php

declare(strict_types=1);

namespace App\Services\Api\V1;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'], // o cast no model User já faz o hash
                'role' => $data['role'],
                'project_id' => $data['project_id'] ?? null,
            ]);
        });
    }
}
