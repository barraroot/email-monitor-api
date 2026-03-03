<?php

namespace App\Policies;

use App\Models\CpanelAccount;
use App\Models\User;

class CpanelAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage_cpanel_accounts');
    }

    public function view(User $user, CpanelAccount $cpanelAccount): bool
    {
        return $user->can('manage_cpanel_accounts');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_cpanel_accounts');
    }

    public function update(User $user, CpanelAccount $cpanelAccount): bool
    {
        return $user->can('manage_cpanel_accounts');
    }

    public function delete(User $user, CpanelAccount $cpanelAccount): bool
    {
        return $user->can('manage_cpanel_accounts');
    }
}
