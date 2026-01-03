<?php

namespace App\Policies;

use App\Models\Car;
use App\Models\User;

/**
 * Car Policy
 * 
 * Defines authorization rules for car operations based on RBAC.
 */
class CarPolicy
{
    /**
     * Determine whether the user can view any cars.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view car list (with role-based filtering)
        return true;
    }

    /**
     * Determine whether the user can view the car.
     */
    public function view(User $user, Car $car): bool
    {
        // Admin can view any car
        if ($user->isAdmin()) {
            return true;
        }

        // Dealer can view their own cars
        if ($user->isDealer()) {
            return $car->user_id === $user->id;
        }

        // Client can view cars assigned to them
        if ($user->isClient()) {
            return $car->client_user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create cars.
     */
    public function create(User $user): bool
    {
        // Only admin can create cars
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the car.
     */
    public function update(User $user, Car $car): bool
    {
        // Admin can update any car
        if ($user->isAdmin()) {
            return true;
        }

        // Dealer can update their own cars (limited fields)
        if ($user->isDealer()) {
            return $car->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the car.
     */
    public function delete(User $user, Car $car): bool
    {
        // Only admin can delete cars
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can upload files to the car.
     */
    public function uploadFiles(User $user, Car $car): bool
    {
        return $this->update($user, $car);
    }

    /**
     * Determine whether the user can set the main photo.
     */
    public function setMainPhoto(User $user, Car $car): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view financial details.
     */
    public function viewFinancials(User $user, Car $car): bool
    {
        // Admin and owner can see financials
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isDealer()) {
            return $car->user_id === $user->id;
        }

        // Clients see limited financial info
        if ($user->isClient()) {
            return $car->client_user_id === $user->id;
        }

        return false;
    }
}
