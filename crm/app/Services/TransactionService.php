<?php

namespace App\Services;

use App\Enums\TransactionPurpose;
use App\Models\Car;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Transaction Service
 * 
 * Handles all financial operations with proper database transactions
 * to prevent race conditions and ensure data integrity.
 */
class TransactionService
{
    /**
     * Transfer amount from user balance to car payment
     * 
     * Uses atomic operations to prevent race conditions:
     * - Deducts from user balance only if sufficient
     * - Adds to car paid amount
     * - Creates transaction record
     * 
     * All operations are wrapped in a database transaction.
     *
     * @throws Exception If insufficient balance or DB error
     */
    public function transferToCarPayment(
        User $user,
        Car $car,
        float $amount,
        ?string $comment = null
    ): Transaction {
        if ($amount <= 0) {
            throw new Exception('თანხა უნდა იყოს 0-ზე მეტი');
        }

        return DB::transaction(function () use ($user, $car, $amount, $comment) {
            // Atomic balance deduction with validation
            // This UPDATE only succeeds if balance >= amount (race-condition safe)
            $affected = DB::table('users')
                ->where('id', $user->id)
                ->where('balance', '>=', $amount)
                ->decrement('balance', $amount);

            if ($affected === 0) {
                throw new Exception('არასაკმარისი ბალანსი');
            }

            // Add to car's paid amount
            $car->increment('paid_amount', $amount);

            // Create transaction record
            return Transaction::create([
                'car_id' => $car->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'payment_date' => now(),
                'purpose' => TransactionPurpose::INTERNAL_TRANSFER,
                'comment' => $comment ?? 'გადახდა ბალანსიდან',
            ]);
        });
    }

    /**
     * Add balance topup for user
     */
    public function addBalanceTopup(
        User $targetUser,
        float $amount,
        ?string $comment = null
    ): Transaction {
        if ($amount <= 0) {
            throw new Exception('თანხა უნდა იყოს 0-ზე მეტი');
        }

        return DB::transaction(function () use ($targetUser, $amount, $comment) {
            // Add to user balance
            $targetUser->increment('balance', $amount);

            // Create transaction record
            return Transaction::create([
                'car_id' => null,
                'user_id' => $targetUser->id,
                'amount' => $amount,
                'payment_date' => now(),
                'purpose' => TransactionPurpose::BALANCE_TOPUP,
                'comment' => $comment,
            ]);
        });
    }

    /**
     * Add direct car payment (vehicle cost or shipping)
     */
    public function addCarPayment(
        Car $car,
        float $amount,
        TransactionPurpose $purpose,
        ?string $comment = null
    ): Transaction {
        if ($amount <= 0) {
            throw new Exception('თანხა უნდა იყოს 0-ზე მეტი');
        }

        return DB::transaction(function () use ($car, $amount, $purpose, $comment) {
            // Add to car's paid amount
            $car->increment('paid_amount', $amount);

            // Create transaction record
            return Transaction::create([
                'car_id' => $car->id,
                'user_id' => $car->user_id,
                'amount' => $amount,
                'payment_date' => now(),
                'purpose' => $purpose,
                'comment' => $comment,
            ]);
        });
    }

    /**
     * Edit existing transaction
     * Adjusts related car/user amounts accordingly
     */
    public function updateTransaction(
        Transaction $transaction,
        float $newAmount,
        ?string $newPurpose = null,
        ?\DateTime $newDate = null,
        ?string $newComment = null
    ): Transaction {
        return DB::transaction(function () use ($transaction, $newAmount, $newPurpose, $newDate, $newComment) {
            $oldAmount = $transaction->amount;
            $diff = $newAmount - $oldAmount;

            // Adjust the related entity
            if ($transaction->car_id) {
                // It's a car payment - adjust paid_amount
                $transaction->car->increment('paid_amount', $diff);
            } else if ($transaction->user_id && $transaction->purpose === TransactionPurpose::BALANCE_TOPUP) {
                // It's a balance topup - adjust user balance
                $transaction->user->increment('balance', $diff);
            }

            // Update transaction record
            $transaction->update([
                'amount' => $newAmount,
                'purpose' => $newPurpose ?? $transaction->purpose,
                'payment_date' => $newDate ?? $transaction->payment_date,
                'comment' => $newComment ?? $transaction->comment,
            ]);

            return $transaction->fresh();
        });
    }

    /**
     * Delete transaction and reverse its effects
     */
    public function deleteTransaction(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            $amount = $transaction->amount;

            // Reverse the effects
            if ($transaction->car_id) {
                // Reduce car's paid amount
                $transaction->car->decrement('paid_amount', $amount);
            } else if ($transaction->user_id && $transaction->purpose === TransactionPurpose::BALANCE_TOPUP) {
                // Reduce user balance
                $transaction->user->decrement('balance', $amount);
            }

            return $transaction->delete();
        });
    }

    /**
     * Get user transactions with related data
     */
    public function getUserTransactions(User $user, int $limit = 10)
    {
        return Transaction::with(['car'])
            ->forUser($user)
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get cars with debt for a user
     */
    public function getCarsWithDebt(User $user)
    {
        return Car::where('user_id', $user->id)
            ->withDebt()
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($car) {
                $car->remaining_debt = $car->debt;
                return $car;
            });
    }
}
