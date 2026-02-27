<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreMailboxRequest;
use App\Http\Requests\Api\V1\UpdateMailboxRequest;
use App\Http\Resources\Api\V1\MailboxResource;
use App\Models\Mailbox;
use App\Services\MailboxService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MailboxController extends Controller
{
    public function index(MailboxService $mailboxService): AnonymousResourceCollection
    {
        $perPage = min((int) request()->query('per_page', 50), 200);

        return MailboxResource::collection(
            $mailboxService->paginate($perPage)
        );
    }

    public function store(StoreMailboxRequest $request, MailboxService $mailboxService): MailboxResource
    {
        $mailbox = $mailboxService->store($request->validated());

        return new MailboxResource($mailbox);
    }

    public function update(UpdateMailboxRequest $request, Mailbox $mailbox, MailboxService $mailboxService): MailboxResource
    {
        $mailbox = $mailboxService->update($mailbox, $request->validated());

        return new MailboxResource($mailbox);
    }
}
