<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MetricsOverviewRequest;
use App\Http\Requests\Api\V1\MetricsQueueRequest;
use App\Http\Requests\Api\V1\MetricsSeriesRequest;
use App\Http\Resources\Api\V1\MetricSeriesResource;
use App\Http\Resources\Api\V1\OverviewResource;
use App\Services\MetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class MetricsController extends Controller
{
    #[OA\Get(
        path: '/api/v1/metrics/overview',
        tags: ['Metrics'],
        summary: 'Overview metrics',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'start_at', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'end_at', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'domain', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'whm_account', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/Overview')
            ),
        ]
    )]
    public function overview(MetricsOverviewRequest $request, MetricsService $metricsService): OverviewResource
    {
        return new OverviewResource($metricsService->overview($request));
    }

    #[OA\Get(
        path: '/api/v1/metrics/mail/series',
        tags: ['Metrics'],
        summary: 'Mail events time series',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'start_at', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'end_at', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'interval', in: 'query', schema: new OA\Schema(type: 'string', enum: ['hour', 'day'])),
            new OA\Parameter(name: 'domain', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'whm_account', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/MetricSeries')
                )
            ),
        ]
    )]
    public function mailSeries(MetricsSeriesRequest $request, MetricsService $metricsService): AnonymousResourceCollection
    {
        return MetricSeriesResource::collection($metricsService->mailSeries($request));
    }

    #[OA\Get(
        path: '/api/v1/metrics/auth/series',
        tags: ['Metrics'],
        summary: 'Auth events time series',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'start_at', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'end_at', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'interval', in: 'query', schema: new OA\Schema(type: 'string', enum: ['hour', 'day'])),
            new OA\Parameter(name: 'domain', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'whm_account', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/MetricSeries')
                )
            ),
        ]
    )]
    public function authSeries(MetricsSeriesRequest $request, MetricsService $metricsService): AnonymousResourceCollection
    {
        return MetricSeriesResource::collection($metricsService->authSeries($request));
    }

    #[OA\Get(
        path: '/api/v1/metrics/queue',
        tags: ['Metrics'],
        summary: 'Queue depth metrics',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'start_at', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'end_at', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'interval', in: 'query', schema: new OA\Schema(type: 'string', enum: ['hour', 'day'])),
            new OA\Parameter(name: 'domain', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'whm_account', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/QueueResponse')
            ),
        ]
    )]
    public function queue(MetricsQueueRequest $request, MetricsService $metricsService): JsonResponse
    {
        $result = $metricsService->queue($request);

        return response()->json([
            'last_queue_depth' => $result['last_queue_depth'],
            'series' => MetricSeriesResource::collection($result['series'])->resolve(),
        ]);
    }
}
