<?php

namespace App\Policies;

use App\Models\AuthEvent;
use App\Models\User;

class AuthEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_auth_events');
    }

    public function view(User $user, AuthEvent $authEvent): bool
    {
        return $user->can('view_auth_events');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, AuthEvent $authEvent): bool
    {
        return false;
    }

    public function delete(User $user, AuthEvent $authEvent): bool
    {
        return false;
    }
}
