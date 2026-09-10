<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            'new' => 'New',
            'read' => 'Read',
        ];
    }

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'admin_notes',
        'read_at',
        'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    public function statusKey(): string
    {
        return $this->status === 'new' ? 'new' : 'read';
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->statusKey()];
    }

    public function statusBadge(): string
    {
        return $this->statusKey() === 'new' ? 'warning' : 'info';
    }

    public function messagePreview(int $length = 80): string
    {
        $text = trim((string) $this->message);

        return $text === '' ? '—' : \Illuminate\Support\Str::limit($text, $length);
    }

    public function markRead(): void
    {
        if ($this->status === 'new') {
            $this->update([
                'status' => 'read',
                'read_at' => $this->read_at ?: now(),
            ]);
        }
    }
}
