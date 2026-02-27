<?php

namespace App\Services;

use App\Models\Mailbox;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MailboxService
{
    public function paginate(int $perPage = 50): LengthAwarePaginator
    {
        return Mailbox::query()
            ->orderBy('email')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): Mailbox
    {
        return Mailbox::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Mailbox $mailbox, array $data): Mailbox
    {
        $mailbox->update($data);

        return $mailbox;
    }
}
