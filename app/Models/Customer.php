<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'customer_group_id',
        'name',
        'email',
        'phone',
        'dob',
        'gender',
        'gstin',
        'company_name',
        'wallet_balance',
        'reward_points',
        'total_orders',
        'total_spent',
        'average_order_value',
        'last_order_at',
        'cancelled_orders',
        'acquisition_source',
        'is_blocked',
        'is_verified',
        'notes',
        'notification_preferences',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'wallet_balance' => 'decimal:2',
            'total_spent' => 'decimal:2',
            'average_order_value' => 'decimal:2',
            'last_order_at' => 'datetime',
            'is_blocked' => 'boolean',
            'is_verified' => 'boolean',
            'reward_points' => 'integer',
            'notification_preferences' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function rewardPointTransactions(): HasMany
    {
        return $this->hasMany(RewardPointTransaction::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(CustomerPaymentMethod::class);
    }

    public function returnRequests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }

    /**
     * @return array{email: bool, order_updates: bool, offers: bool}
     */
    public function notificationPrefs(): array
    {
        $stored = is_array($this->notification_preferences) ? $this->notification_preferences : [];

        return [
            'email' => (bool) ($stored['email'] ?? true),
            'order_updates' => (bool) ($stored['order_updates'] ?? true),
            'offers' => (bool) ($stored['offers'] ?? true),
        ];
    }

    public function membershipLabel(): string
    {
        $group = $this->group?->name;
        if ($group) {
            return str_contains(strtolower($group), 'member') ? $group : $group.' Member';
        }

        if ((float) $this->total_spent >= 50000 || (int) $this->reward_points >= 500) {
            return 'Gold Member';
        }

        if ((float) $this->total_spent >= 10000) {
            return 'Silver Member';
        }

        return 'Member';
    }

    public function statusLabel(): string
    {
        return $this->is_blocked ? 'Blocked' : 'Active';
    }

    public function statusBadge(): string
    {
        return $this->is_blocked ? 'inactive' : 'active';
    }

    public function customerNotes(): HasMany
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function communicationLogs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class);
    }

    public static function forUser(User $user, bool $create = true): ?self
    {
        $match = static::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);
                if (filled($user->email)) {
                    $query->orWhereRaw('LOWER(email) = ?', [strtolower((string) $user->email)]);
                }
            })
            ->orderBy('id')
            ->first();

        if ($match) {
            if (! $match->user_id) {
                $match->update(['user_id' => $user->id]);
            }
            $user->setRelation('customer', $match);

            return $match;
        }

        if (! $create) {
            return null;
        }

        $customer = static::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->mobile ?: $user->phone,
                'is_verified' => true,
                'acquisition_source' => 'website',
            ]
        );

        $user->setRelation('customer', $customer);

        return $customer;
    }
}
