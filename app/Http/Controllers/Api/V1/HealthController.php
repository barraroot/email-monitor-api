<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class HealthController extends Controller
{
    #[OA\Get(
        path: '/api/v1/health',
        tags: ['Health'],
        summary: 'Health check',
        security: [],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/HealthResponse')
            ),
        ]
    )]
    public function show(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'version' => config('app.version'),
            'time' => now()->toIso8601String(),
        ]);
    }
}
