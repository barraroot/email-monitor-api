<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\AuthEventController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\IngestController;
use App\Http\Controllers\Api\V1\MailboxController;
use App\Http\Controllers\Api\V1\MailEventController;
use App\Http\Controllers\Api\V1\MetricsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', [HealthController::class, 'show']);

    Route::prefix('auth')->middleware('throttle:auth')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/token', [AuthController::class, 'token']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });

    Route::post('/ingest/events', [IngestController::class, 'store']);//->middleware('throttle:ingest');

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
        Route::get('/mail/events', [MailEventController::class, 'index']);
        Route::get('/auth/events', [AuthEventController::class, 'index']);

        Route::get('/metrics/overview', [MetricsController::class, 'overview']);
        Route::get('/metrics/mail/series', [MetricsController::class, 'mailSeries']);
        Route::get('/metrics/auth/series', [MetricsController::class, 'authSeries']);
        Route::get('/metrics/queue', [MetricsController::class, 'queue']);

        Route::get('/mailboxes', [MailboxController::class, 'index']);
        Route::post('/mailboxes', [MailboxController::class, 'store']);
        Route::patch('/mailboxes/{mailbox}', [MailboxController::class, 'update']);
    });
});
