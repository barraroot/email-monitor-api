<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Mail Monitor API',
    version: '1.0.0',
    description: 'API for Exim/Dovecot monitoring and metrics.'
)]
#[OA\Server(
    url: '/',
    description: 'Current host'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token'
)]
#[OA\Tag(name: 'Health', description: 'Health checks')]
#[OA\Tag(name: 'Auth', description: 'Authentication')]
#[OA\Tag(name: 'Ingest', description: 'Ingest events')]
#[OA\Tag(name: 'Mail Events', description: 'Mail events')]
#[OA\Tag(name: 'Auth Events', description: 'Auth events')]
#[OA\Tag(name: 'Metrics', description: 'Aggregated metrics')]
#[OA\Tag(name: 'Mailboxes', description: 'Mailboxes')]
#[OA\Schema(
    schema: 'HealthResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'ok'),
        new OA\Property(property: 'version', type: 'string', example: '1.0.0'),
        new OA\Property(property: 'time', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'User',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Admin'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'CpanelAccount',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 10),
        new OA\Property(property: 'whm_account', type: 'string', example: 'rmlad479'),
        new OA\Property(property: 'domain', type: 'string', example: 'rmladv.com.br'),
        new OA\Property(property: 'cpanel_host', type: 'string', example: 'https://server:2087'),
    ]
)]
#[OA\Schema(
    schema: 'AuthRegisterResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'token', type: 'string'),
        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
        new OA\Property(property: 'cpanel_account', ref: '#/components/schemas/CpanelAccount'),
    ]
)]
#[OA\Schema(
    schema: 'AuthLoginResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'token', type: 'string'),
        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
    ]
)]
#[OA\Schema(
    schema: 'RegisterRequest',
    type: 'object',
    required: ['name', 'email', 'password', 'whm_account', 'domain', 'cpanel_host', 'cpanel_api_token'],
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'password', type: 'string', format: 'password'),
        new OA\Property(property: 'whm_account', type: 'string'),
        new OA\Property(property: 'domain', type: 'string'),
        new OA\Property(property: 'cpanel_host', type: 'string'),
        new OA\Property(property: 'cpanel_api_token', type: 'string'),
        new OA\Property(property: 'cpanel_username', type: 'string'),
    ]
)]
#[OA\Schema(
    schema: 'LoginRequest',
    type: 'object',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'password', type: 'string', format: 'password'),
    ]
)]
#[OA\Schema(
    schema: 'IngestMailEvent',
    type: 'object',
    required: ['occurred_at', 'direction', 'event_type'],
    properties: [
        new OA\Property(property: 'occurred_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'direction', type: 'string', enum: ['inbound', 'outbound', 'local']),
        new OA\Property(property: 'event_type', type: 'string'),
        new OA\Property(property: 'exim_message_id', type: 'string'),
        new OA\Property(property: 'sender', type: 'string'),
        new OA\Property(property: 'recipient', type: 'string'),
        new OA\Property(property: 'domain', type: 'string'),
        new OA\Property(property: 'whm_account', type: 'string'),
        new OA\Property(property: 'ip', type: 'string'),
        new OA\Property(property: 'remote_mta', type: 'string'),
        new OA\Property(property: 'smtp_code', type: 'string'),
        new OA\Property(property: 'smtp_response', type: 'string'),
        new OA\Property(property: 'status', type: 'string'),
        new OA\Property(property: 'error_category', type: 'string'),
        new OA\Property(property: 'error_message', type: 'string'),
        new OA\Property(property: 'meta', type: 'object'),
    ]
)]
#[OA\Schema(
    schema: 'IngestAuthEvent',
    type: 'object',
    required: ['occurred_at', 'proto', 'event_type', 'auth_result'],
    properties: [
        new OA\Property(property: 'occurred_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'proto', type: 'string', enum: ['imap', 'pop3', 'smtp']),
        new OA\Property(property: 'event_type', type: 'string'),
        new OA\Property(property: 'user_email', type: 'string', format: 'email'),
        new OA\Property(property: 'domain', type: 'string'),
        new OA\Property(property: 'whm_account', type: 'string'),
        new OA\Property(property: 'ip', type: 'string'),
        new OA\Property(property: 'auth_result', type: 'string', enum: ['success', 'fail']),
        new OA\Property(property: 'failure_reason', type: 'string'),
        new OA\Property(property: 'meta', type: 'object'),
    ]
)]
#[OA\Schema(
    schema: 'IngestEventsRequest',
    type: 'object',
    required: ['source', 'domain', 'whm_account'],
    properties: [
        new OA\Property(property: 'source', type: 'string'),
        new OA\Property(property: 'domain', type: 'string'),
        new OA\Property(property: 'whm_account', type: 'string'),
        new OA\Property(
            property: 'mail_events',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/IngestMailEvent')
        ),
        new OA\Property(
            property: 'auth_events',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/IngestAuthEvent')
        ),
    ]
)]
#[OA\Schema(
    schema: 'IngestSummary',
    type: 'object',
    properties: [
        new OA\Property(property: 'received', type: 'integer'),
        new OA\Property(property: 'inserted', type: 'integer'),
        new OA\Property(property: 'duplicates', type: 'integer'),
        new OA\Property(property: 'invalid', type: 'integer'),
    ]
)]
#[OA\Schema(
    schema: 'IngestResult',
    type: 'object',
    properties: [
        new OA\Property(property: 'source', type: 'string'),
        new OA\Property(property: 'mail_events', ref: '#/components/schemas/IngestSummary'),
        new OA\Property(property: 'auth_events', ref: '#/components/schemas/IngestSummary'),
    ]
)]
#[OA\Schema(
    schema: 'MailEvent',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'occurred_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'direction', type: 'string'),
        new OA\Property(property: 'event_type', type: 'string'),
        new OA\Property(property: 'exim_message_id', type: 'string'),
        new OA\Property(property: 'sender', type: 'string'),
        new OA\Property(property: 'recipient', type: 'string'),
        new OA\Property(property: 'domain', type: 'string'),
        new OA\Property(property: 'whm_account', type: 'string'),
        new OA\Property(property: 'ip', type: 'string'),
        new OA\Property(property: 'remote_mta', type: 'string'),
        new OA\Property(property: 'smtp_code', type: 'string'),
        new OA\Property(property: 'smtp_response', type: 'string'),
        new OA\Property(property: 'status', type: 'string'),
        new OA\Property(property: 'error_category', type: 'string'),
        new OA\Property(property: 'error_message', type: 'string'),
        new OA\Property(property: 'meta', type: 'object'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'AuthEvent',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'occurred_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'proto', type: 'string'),
        new OA\Property(property: 'event_type', type: 'string'),
        new OA\Property(property: 'user_email', type: 'string'),
        new OA\Property(property: 'domain', type: 'string'),
        new OA\Property(property: 'whm_account', type: 'string'),
        new OA\Property(property: 'ip', type: 'string'),
        new OA\Property(property: 'auth_result', type: 'string'),
        new OA\Property(property: 'failure_reason', type: 'string'),
        new OA\Property(property: 'meta', type: 'object'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'Mailbox',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'whm_account', type: 'string'),
        new OA\Property(property: 'domain', type: 'string'),
        new OA\Property(property: 'email', type: 'string'),
        new OA\Property(property: 'is_active', type: 'boolean'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'MetricPoint',
    type: 'object',
    properties: [
        new OA\Property(property: 't', type: 'string', format: 'date-time'),
        new OA\Property(property: 'value', type: 'integer', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'MetricSeries',
    type: 'object',
    properties: [
        new OA\Property(property: 'label', type: 'string'),
        new OA\Property(
            property: 'points',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/MetricPoint')
        ),
    ]
)]
#[OA\Schema(
    schema: 'Overview',
    type: 'object',
    properties: [
        new OA\Property(property: 'inbound_accepted', type: 'integer'),
        new OA\Property(property: 'inbound_rejected', type: 'integer'),
        new OA\Property(property: 'outbound_sent', type: 'integer'),
        new OA\Property(property: 'outbound_deferred', type: 'integer'),
        new OA\Property(property: 'outbound_bounced', type: 'integer'),
        new OA\Property(property: 'login_success', type: 'integer'),
        new OA\Property(property: 'login_failed', type: 'integer'),
        new OA\Property(
            property: 'top_error_categories',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'label', type: 'string'),
                    new OA\Property(property: 'value', type: 'integer'),
                ]
            )
        ),
        new OA\Property(
            property: 'top_failure_reasons',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'label', type: 'string'),
                    new OA\Property(property: 'value', type: 'integer'),
                ]
            )
        ),
    ]
)]
#[OA\Schema(
    schema: 'PaginatedMailEvents',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/MailEvent')
        ),
        new OA\Property(property: 'meta', type: 'object'),
        new OA\Property(property: 'links', type: 'object'),
    ]
)]
#[OA\Schema(
    schema: 'PaginatedAuthEvents',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/AuthEvent')
        ),
        new OA\Property(property: 'meta', type: 'object'),
        new OA\Property(property: 'links', type: 'object'),
    ]
)]
#[OA\Schema(
    schema: 'PaginatedMailboxes',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Mailbox')
        ),
        new OA\Property(property: 'meta', type: 'object'),
        new OA\Property(property: 'links', type: 'object'),
    ]
)]
#[OA\Schema(
    schema: 'MailboxStoreRequest',
    type: 'object',
    required: ['whm_account', 'domain', 'email'],
    properties: [
        new OA\Property(property: 'whm_account', type: 'string'),
        new OA\Property(property: 'domain', type: 'string'),
        new OA\Property(property: 'email', type: 'string'),
        new OA\Property(property: 'is_active', type: 'boolean'),
    ]
)]
#[OA\Schema(
    schema: 'MailboxUpdateRequest',
    type: 'object',
    properties: [
        new OA\Property(property: 'whm_account', type: 'string'),
        new OA\Property(property: 'domain', type: 'string'),
        new OA\Property(property: 'email', type: 'string'),
        new OA\Property(property: 'is_active', type: 'boolean'),
    ]
)]
#[OA\Schema(
    schema: 'QueueResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'last_queue_depth', type: 'integer', nullable: true),
        new OA\Property(
            property: 'series',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/MetricSeries')
        ),
    ]
)]
#[OA\Schema(
    schema: 'ErrorResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]
)]
#[OA\Schema(
    schema: 'ValidationError',
    type: 'object',
    properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'errors', type: 'object'),
    ]
)]
final class EmailMonitorApi {}
