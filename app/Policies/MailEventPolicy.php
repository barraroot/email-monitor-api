<?php

namespace App\Policies;

use App\Models\MailEvent;
use App\Models\User;

class MailEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_mail_events');
    }

    public function view(User $user, MailEvent $mailEvent): bool
    {
        return $user->can('view_mail_events');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, MailEvent $mailEvent): bool
    {
        return false;
    }

    public function delete(User $user, MailEvent $mailEvent): bool
    {
        return false;
    }
}
