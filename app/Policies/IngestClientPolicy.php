<?php

namespace App\Policies;

use App\Models\IngestClient;
use App\Models\User;

class IngestClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage_ingest_clients');
    }

    public function view(User $user, IngestClient $ingestClient): bool
    {
        return $user->can('manage_ingest_clients');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_ingest_clients');
    }

    public function update(User $user, IngestClient $ingestClient): bool
    {
        return $user->can('manage_ingest_clients');
    }

    public function delete(User $user, IngestClient $ingestClient): bool
    {
        return $user->can('manage_ingest_clients');
    }
}
