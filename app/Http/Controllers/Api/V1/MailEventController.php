<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListMailEventsRequest;
use App\Http\Resources\Api\V1\MailEventResource;
use App\Services\MailEventService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MailEventController extends Controller
{
    public function index(ListMailEventsRequest $request, MailEventService $mailEventService): AnonymousResourceCollection
    {
        $paginator = $mailEventService->paginate($request);

        return MailEventResource::collection($paginator);
    }
}
