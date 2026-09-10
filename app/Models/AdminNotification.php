<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'link',
        'data',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function isAlert(): bool
    {
        return in_array((string) $this->type, \App\Services\NotificationService::alertTypes(), true);
    }

    public function url(): string
    {
        $data = is_array($this->data) ? $this->data : [];

        try {
            if (! empty($data['order_id']) && \Illuminate\Support\Facades\Route::has('admin.orders.show')) {
                return route('admin.orders.show', $data['order_id']);
            }
            if (! empty($data['return_id']) && \Illuminate\Support\Facades\Route::has('admin.returns.show')) {
                return route('admin.returns.show', $data['return_id']);
            }
        } catch (\Throwable) {
            // fall through
        }

        if ($this->link) {
            return str_starts_with((string) $this->link, 'http')
                ? (string) $this->link
                : url(ltrim((string) $this->link, '/'));
        }

        return route('admin.notifications.index');
    }

    public function timeAgo(): string
    {
        return $this->created_at?->diffForHumans() ?: '';
    }

    /**
     * @return array<string, mixed>
     */
    public function toFeed(): array
    {
        return [
            'id' => $this->id,
            'type' => (string) $this->type,
            'title' => (string) $this->title,
            'message' => (string) ($this->message ?: ''),
            'url' => $this->url(),
            'read' => (bool) $this->is_read,
            'alert' => $this->isAlert(),
            'time' => $this->timeAgo(),
            'read_url' => route('admin.notifications.read', $this),
        ];
    }
}
