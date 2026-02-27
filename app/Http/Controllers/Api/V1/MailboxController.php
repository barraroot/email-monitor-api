<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreMailboxRequest;
use App\Http\Requests\Api\V1\UpdateMailboxRequest;
use App\Http\Resources\Api\V1\MailboxResource;
use App\Models\Mailbox;
use App\Services\MailboxService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class MailboxController extends Controller
{
    #[OA\Get(
        path: '/api/v1/mailboxes',
        tags: ['Mailboxes'],
        summary: 'List mailboxes',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', maximum: 200)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedMailboxes')
            ),
        ]
    )]
    public function index(MailboxService $mailboxService): AnonymousResourceCollection
    {
        $perPage = min((int) request()->query('per_page', 50), 200);

        return MailboxResource::collection(
            $mailboxService->paginate($perPage)
        );
    }

    #[OA\Post(
        path: '/api/v1/mailboxes',
        tags: ['Mailboxes'],
        summary: 'Create mailbox',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/MailboxStoreRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/Mailbox')
            ),
        ]
    )]
    public function store(StoreMailboxRequest $request, MailboxService $mailboxService): MailboxResource
    {
        $mailbox = $mailboxService->store($request->validated());

        return new MailboxResource($mailbox);
    }

    #[OA\Patch(
        path: '/api/v1/mailboxes/{mailbox}',
        tags: ['Mailboxes'],
        summary: 'Update mailbox',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'mailbox', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/MailboxUpdateRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/Mailbox')
            ),
        ]
    )]
    public function update(UpdateMailboxRequest $request, Mailbox $mailbox, MailboxService $mailboxService): MailboxResource
    {
        $mailbox = $mailboxService->update($mailbox, $request->validated());

        return new MailboxResource($mailbox);
    }
}
