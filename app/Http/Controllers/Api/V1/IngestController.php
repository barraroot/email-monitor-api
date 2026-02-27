<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IngestEventsRequest;
use App\Http\Resources\Api\V1\IngestResultResource;
use App\Services\IngestService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class IngestController extends Controller
{
    #[OA\Post(
        path: '/api/v1/ingest/events',
        tags: ['Ingest'],
        summary: 'Ingest mail and auth events',
        security: [],
        parameters: [
            new OA\Parameter(
                name: 'X-Ingest-Secret',
                in: 'header',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/IngestEventsRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Created',
                content: new OA\JsonContent(ref: '#/components/schemas/IngestResult')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function store(IngestEventsRequest $request, IngestService $ingestService): JsonResponse
    {
        $result = $ingestService->ingest($request->validated());

        return (new IngestResultResource($result))
            ->response()
            ->setStatusCode(201);
    }
}
