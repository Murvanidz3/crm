<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Client Service
 * 
 * Handles client user creation and management.
 */
class ClientService
{
    /**
     * Find existing client or create new one
     * 
     * @param string $idNumber Personal ID number (used as username)
     * @param string $name Client's full name
     * @param string $phone Client's phone number
     * @param string|null $lotNumber Lot number (used as initial password if provided)
     * @return int Client user ID
     */
    public function findOrCreateClient(
        string $idNumber,
        string $name,
        string $phone,
        ?string $lotNumber = null
    ): int {
        // Check if client already exists
        $existingClient = User::where('username', $idNumber)->first();

        if ($existingClient) {
            // Optionally update client info
            $existingClient->update([
                'full_name' => $name ?: $existingClient->full_name,
                'phone' => $phone ?: $existingClient->phone,
            ]);
            
            return $existingClient->id;
        }

        // Create new client
        // Password = lot number if provided, otherwise ID number
        $password = $lotNumber ?: $idNumber;

        $client = User::create([
            'username' => $idNumber,
            'full_name' => $name,
            'phone' => $phone,
            'password' => Hash::make($password),
            'role' => UserRole::CLIENT,
            'sms_enabled' => true,
        ]);

        return $client->id;
    }

    /**
     * Get all clients
     */
    public function getAllClients()
    {
        return User::clients()->orderBy('id', 'desc')->get();
    }

    /**
     * Update client information
     */
    public function updateClient(User $client, array $data): User
    {
        $allowedFields = ['full_name', 'phone', 'sms_enabled'];
        
        $client->update(
            collect($data)->only($allowedFields)->toArray()
        );

        return $client->fresh();
    }
}
