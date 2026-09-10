<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\RewardPointTransaction;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Wishlist;
use App\Repositories\CustomerRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CustomerService extends BaseService
{
    public function __construct(CustomerRepository $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $password = filled($data['password'] ?? null) ? $data['password'] : Str::random(12);
            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'is_blocked' => false,
                'is_verified' => true,
            ];

            $user = User::query()->where('email', $payload['email'])->first();
            if (! $user) {
                $user = User::create([
                    'name' => $payload['name'],
                    'email' => $payload['email'],
                    'password' => $password,
                    'phone' => $payload['phone'],
                    'is_active' => true,
                    'is_staff' => false,
                ]);
            } else {
                $user->update([
                    'name' => $payload['name'],
                    'phone' => $payload['phone'] ?: $user->phone,
                    'is_active' => true,
                ]);
            }

            $payload['user_id'] = $user->id;

            /** @var Customer $customer */
            $customer = parent::create($payload);

            return $customer->fresh();
        });
    }

    public function update(Model $model, array $data): Customer
    {
        /** @var Customer $model */
        return DB::transaction(function () use ($model, $data) {
            $password = $data['password'] ?? null;
            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ];
            if (array_key_exists('is_blocked', $data)) {
                $payload['is_blocked'] = (bool) $data['is_blocked'];
            }

            $model = parent::update($model, $payload);

            if ($model->user) {
                $userData = [
                    'name' => $model->name,
                    'email' => $model->email,
                    'phone' => $model->phone,
                    'is_active' => ! $model->is_blocked,
                ];
                if (filled($password)) {
                    $userData['password'] = $password;
                }
                $model->user->update($userData);
            }

            return $model->fresh();
        });
    }

    public function block(Customer $customer, ?string $reason = null): Customer
    {
        $customer->update(['is_blocked' => true]);

        if ($reason) {
            CustomerNote::create([
                'customer_id' => $customer->id,
                'user_id' => Auth::id(),
                'note' => 'Blocked: ' . $reason,
            ]);
        }

        if ($customer->user) {
            $customer->user->update(['is_active' => false]);
        }

        return $customer->fresh();
    }

    public function unblock(Customer $customer): Customer
    {
        $customer->update(['is_blocked' => false]);

        if ($customer->user) {
            $customer->user->update(['is_active' => true]);
        }

        CustomerNote::create([
            'customer_id' => $customer->id,
            'user_id' => Auth::id(),
            'note' => 'Customer unblocked',
        ]);

        return $customer->fresh();
    }

    public function resetPassword(Customer $customer, ?string $password = null): string
    {
        if (! $customer->user) {
            throw new InvalidArgumentException('Customer has no linked user account.');
        }

        $password ??= Str::password(12);

        $customer->user->update([
            'password' => $password,
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        CustomerNote::create([
            'customer_id' => $customer->id,
            'user_id' => Auth::id(),
            'note' => 'Password reset by admin',
        ]);

        return $password;
    }

    public function addStoreCredit(Customer $customer, float $amount, string $reason = 'Store credit'): WalletTransaction
    {
        if ($amount == 0.0) {
            throw new InvalidArgumentException('Amount cannot be zero.');
        }

        return DB::transaction(function () use ($customer, $amount, $reason) {
            $customer->refresh();
            $type = $amount > 0 ? 'credit' : 'debit';
            $abs = abs($amount);
            $newBalance = (float) $customer->wallet_balance + $amount;

            if ($newBalance < 0) {
                throw new InvalidArgumentException('Insufficient wallet balance.');
            }

            $customer->update(['wallet_balance' => round($newBalance, 2)]);

            return WalletTransaction::create([
                'customer_id' => $customer->id,
                'type' => $type,
                'amount' => $abs,
                'balance_after' => round($newBalance, 2),
                'reason' => $reason,
                'created_by' => Auth::id(),
            ]);
        });
    }

    public function addRewardPoints(Customer $customer, int $points, string $reason = 'Reward points'): RewardPointTransaction
    {
        if ($points === 0) {
            throw new InvalidArgumentException('Points cannot be zero.');
        }

        return DB::transaction(function () use ($customer, $points, $reason) {
            $customer->refresh();
            $type = $points > 0 ? 'credit' : 'debit';
            $newBalance = (int) $customer->reward_points + $points;

            if ($newBalance < 0) {
                throw new InvalidArgumentException('Insufficient reward points.');
            }

            $customer->update(['reward_points' => $newBalance]);

            return RewardPointTransaction::create([
                'customer_id' => $customer->id,
                'type' => $type,
                'points' => abs($points),
                'balance_after' => $newBalance,
                'reason' => $reason,
                'created_by' => Auth::id(),
            ]);
        });
    }

    public function mergeCustomers(Customer $primary, Customer $secondary): Customer
    {
        if ($primary->id === $secondary->id) {
            throw new InvalidArgumentException('Cannot merge a customer with itself.');
        }

        return DB::transaction(function () use ($primary, $secondary) {
            $this->moveCustomerRecords($secondary, $primary);

            $primary->update([
                'wallet_balance' => (float) $primary->wallet_balance + (float) $secondary->wallet_balance,
                'reward_points' => (int) $primary->reward_points + (int) $secondary->reward_points,
                'total_orders' => (int) $primary->total_orders + (int) $secondary->total_orders,
                'total_spent' => (float) $primary->total_spent + (float) $secondary->total_spent,
                'cancelled_orders' => (int) $primary->cancelled_orders + (int) $secondary->cancelled_orders,
            ]);

            if ((int) $primary->total_orders > 0) {
                $primary->update([
                    'average_order_value' => round((float) $primary->total_spent / (int) $primary->total_orders, 2),
                ]);
            }

            $secondary->delete();

            return $primary->fresh();
        });
    }

    public function collapseDuplicates(): void
    {
        $userIds = Customer::query()
            ->whereNotNull('user_id')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            $rows = Customer::query()->where('user_id', $userId)->orderBy('id')->get();
            $primary = $rows->shift();
            foreach ($rows as $extra) {
                $this->mergeCustomers($primary, $extra);
            }
        }

        $emails = Customer::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->selectRaw('LOWER(email) as email_key')
            ->groupBy('email_key')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('email_key');

        foreach ($emails as $email) {
            $rows = Customer::query()->whereRaw('LOWER(email) = ?', [$email])->orderBy('id')->get();
            $primary = $rows->shift();
            foreach ($rows as $extra) {
                $this->mergeCustomers($primary, $extra);
            }
        }
    }

    public function assignGroup(Customer $customer, ?int $groupId): Customer
    {
        $customer->update(['customer_group_id' => $groupId]);

        return $customer->fresh(['group']);
    }

    private function moveCustomerRecords(Customer $from, Customer $to): void
    {
        $fromId = $from->id;
        $toId = $to->id;

        $ownedProductIds = Wishlist::query()->where('customer_id', $toId)->pluck('product_id');
        Wishlist::query()
            ->where('customer_id', $fromId)
            ->whereIn('product_id', $ownedProductIds)
            ->delete();

        $tables = [
            'customer_addresses',
            'orders',
            'wallet_transactions',
            'reward_point_transactions',
            'wishlists',
            'customer_notes',
            'communication_logs',
            'customer_payment_methods',
            'returns',
            'carts',
            'cart_items',
            'abandoned_carts',
            'reviews',
            'refunds',
            'payments',
            'support_tickets',
            'product_questions',
            'price_alerts',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'customer_id')) {
                DB::table($table)->where('customer_id', $fromId)->update(['customer_id' => $toId]);
            }
        }
    }
}
