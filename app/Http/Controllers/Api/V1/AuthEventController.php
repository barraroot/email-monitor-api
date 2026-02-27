<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListAuthEventsRequest;
use App\Http\Resources\Api\V1\AuthEventResource;
use App\Services\AuthEventService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class AuthEventController extends Controller
{
    #[OA\Get(
        path: '/api/v1/auth/events',
        tags: ['Auth Events'],
        summary: 'List auth events',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'start_at', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'end_at', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'domain', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'whm_account', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'proto', in: 'query', schema: new OA\Schema(type: 'string', enum: ['imap', 'pop3', 'smtp'])),
            new OA\Parameter(name: 'auth_result', in: 'query', schema: new OA\Schema(type: 'string', enum: ['success', 'fail'])),
            new OA\Parameter(name: 'user_email', in: 'query', schema: new OA\Schema(type: 'string', format: 'email')),
            new OA\Parameter(name: 'ip', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', maximum: 200)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedAuthEvents')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function index(ListAuthEventsRequest $request, AuthEventService $authEventService): AnonymousResourceCollection
    {
        $paginator = $authEventService->paginate($request);

        return AuthEventResource::collection($paginator);
    }
}
