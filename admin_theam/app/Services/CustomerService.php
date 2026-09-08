<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\RewardPointTransaction;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Repositories\CustomerRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
            $password = $data['password'] ?? Str::random(12);
            unset($data['password']);

            if (! empty($data['email']) && empty($data['user_id'])) {
                $user = User::firstOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'password' => Hash::make($password),
                        'phone' => $data['phone'] ?? null,
                        'is_active' => true,
                        'is_staff' => false,
                    ]
                );
                $data['user_id'] = $user->id;
            }

            /** @var Customer $customer */
            $customer = parent::create($data);

            return $customer->fresh(['group', 'addresses']);
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
            'password' => Hash::make($password),
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
            $secondary->addresses()->update(['customer_id' => $primary->id]);
            $secondary->orders()->update(['customer_id' => $primary->id]);
            $secondary->walletTransactions()->update(['customer_id' => $primary->id]);
            $secondary->rewardPointTransactions()->update(['customer_id' => $primary->id]);
            $secondary->wishlists()->update(['customer_id' => $primary->id]);
            $secondary->customerNotes()->update(['customer_id' => $primary->id]);
            $secondary->communicationLogs()->update(['customer_id' => $primary->id]);

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

            CustomerNote::create([
                'customer_id' => $primary->id,
                'user_id' => Auth::id(),
                'note' => 'Merged customer #' . $secondary->id . ' (' . $secondary->email . ')',
            ]);

            $secondary->delete();

            return $primary->fresh();
        });
    }

    public function assignGroup(Customer $customer, ?int $groupId): Customer
    {
        $customer->update(['customer_group_id' => $groupId]);

        return $customer->fresh(['group']);
    }
}
