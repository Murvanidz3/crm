<?php

namespace App\Policies;

use App\Models\User;

/**
 * User Policy
 * 
 * Defines authorization rules for user management.
 */
class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the user.
     */
    public function view(User $user, User $model): bool
    {
        // Admin can view any user
        if ($user->isAdmin()) {
            return true;
        }

        // Users can view their own profile
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the user.
     */
    public function update(User $user, User $model): bool
    {
        // Admin can update any user
        if ($user->isAdmin()) {
            return true;
        }

        // Users can update their own password
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the user.
     */
    public function delete(User $user, User $model): bool
    {
        // Only admin can delete
        // Cannot delete self
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can toggle SMS settings.
     */
    public function toggleSms(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can reset password.
     */
    public function resetPassword(User $user, User $model): bool
    {
        // Admin can reset any password
        // Users can change their own
        return $user->isAdmin() || $user->id === $model->id;
    }

    /**
     * Determine whether the user can manage balance.
     */
    public function manageBalance(User $user, User $model): bool
    {
        return $user->isAdmin();
    }
}
