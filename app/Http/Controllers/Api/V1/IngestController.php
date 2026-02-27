<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IngestEventsRequest;
use App\Http\Resources\Api\V1\IngestResultResource;
use App\Services\IngestService;
use Illuminate\Http\JsonResponse;

class IngestController extends Controller
{
    public function store(IngestEventsRequest $request, IngestService $ingestService): JsonResponse
    {
        $result = $ingestService->ingest($request->validated());

        return (new IngestResultResource($result))
            ->response()
            ->setStatusCode(201);
    }
}
