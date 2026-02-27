<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListMailEventsRequest;
use App\Http\Resources\Api\V1\MailEventResource;
use App\Services\MailEventService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class MailEventController extends Controller
{
    #[OA\Get(
        path: '/api/v1/mail/events',
        tags: ['Mail Events'],
        summary: 'List mail events',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'start_at', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'end_at', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'domain', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'whm_account', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'direction', in: 'query', schema: new OA\Schema(type: 'string', enum: ['inbound', 'outbound', 'local'])),
            new OA\Parameter(name: 'event_type', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'error_category', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sender', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'recipient', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', maximum: 200)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedMailEvents')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function index(ListMailEventsRequest $request, MailEventService $mailEventService): AnonymousResourceCollection
    {
        $paginator = $mailEventService->paginate($request);

        return MailEventResource::collection($paginator);
    }
}
