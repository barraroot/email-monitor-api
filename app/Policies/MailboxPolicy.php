<?php

namespace App\Policies;

use App\Models\Mailbox;
use App\Models\User;

class MailboxPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage_mailboxes') || $user->can('view_mail_events');
    }

    public function view(User $user, Mailbox $mailbox): bool
    {
        return $user->can('manage_mailboxes') || $user->can('view_mail_events');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_mailboxes');
    }

    public function update(User $user, Mailbox $mailbox): bool
    {
        return $user->can('manage_mailboxes');
    }

    public function delete(User $user, Mailbox $mailbox): bool
    {
        return $user->can('manage_mailboxes');
    }
}
