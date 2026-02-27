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

class MetricsController extends Controller
{
    public function overview(MetricsOverviewRequest $request, MetricsService $metricsService): OverviewResource
    {
        return new OverviewResource($metricsService->overview($request));
    }

    public function mailSeries(MetricsSeriesRequest $request, MetricsService $metricsService): AnonymousResourceCollection
    {
        return MetricSeriesResource::collection($metricsService->mailSeries($request));
    }

    public function authSeries(MetricsSeriesRequest $request, MetricsService $metricsService): AnonymousResourceCollection
    {
        return MetricSeriesResource::collection($metricsService->authSeries($request));
    }

    public function queue(MetricsQueueRequest $request, MetricsService $metricsService): JsonResponse
    {
        $result = $metricsService->queue($request);

        return response()->json([
            'last_queue_depth' => $result['last_queue_depth'],
            'series' => MetricSeriesResource::collection($result['series'])->resolve(),
        ]);
    }
}
