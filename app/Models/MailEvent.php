<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailEvent extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'occurred_at',
        'direction',
        'event_type',
        'exim_message_id',
        'sender',
        'recipient',
        'domain',
        'whm_account',
        'ip',
        'remote_mta',
        'smtp_code',
        'smtp_response',
        'status',
        'error_category',
        'error_message',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'meta' => 'array',
        ];
    }
}
