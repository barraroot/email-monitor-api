<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListAuthEventsRequest;
use App\Http\Resources\Api\V1\AuthEventResource;
use App\Services\AuthEventService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuthEventController extends Controller
{
    public function index(ListAuthEventsRequest $request, AuthEventService $authEventService): AnonymousResourceCollection
    {
        $paginator = $authEventService->paginate($request);

        return AuthEventResource::collection($paginator);
    }
}
