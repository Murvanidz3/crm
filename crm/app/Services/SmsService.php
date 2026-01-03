<?php

namespace App\Services;

use App\Enums\CarStatus;
use App\Models\Car;
use App\Models\Notification;
use App\Models\SmsTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SMS Service
 * 
 * Handles SMS sending and notifications for car status changes.
 * Uses smsoffice.ge API.
 */
class SmsService
{
    private string $apiKey;
    private string $sender;
    private bool $enabled;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.sms.api_key', '');
        $this->sender = config('services.sms.sender', 'ONECAR.GE');
        $this->enabled = config('services.sms.enabled', true);
        $this->baseUrl = config('services.sms.base_url', 'https://smsoffice.ge/api/v2/send/');
    }

    /**
     * Send SMS notification for car status change
     */
    public function sendStatusNotification(Car $car, CarStatus|string $status): bool
    {
        if (is_string($status)) {
            $status = CarStatus::tryFrom($status);
        }

        if (!$status) {
            return false;
        }

        // Get template for this status
        $template = SmsTemplate::getForStatus($status);
        
        if (!$template) {
            return false;
        }

        // Compile message with car data
        $message = $template->compile($car);

        // Determine target phone number
        $targetPhone = $this->getTargetPhone($car);
        
        if (!$targetPhone) {
            return false;
        }

        // Create in-app notification
        $this->createNotification($car, $message);

        // Send SMS
        return $this->sendSms($targetPhone, $message);
    }

    /**
     * Send SMS to phone number
     */
    public function sendSms(string $phone, string $message): bool
    {
        // Clean phone number
        $phone = $this->cleanPhoneNumber($phone);

        if (!$phone || strlen($phone) < 11) {
            Log::warning('Invalid phone number for SMS', ['phone' => $phone]);
            return false;
        }

        if (!$this->enabled || empty($this->apiKey)) {
            Log::info('SMS disabled or no API key', ['phone' => $phone, 'message' => $message]);
            return $this->logSms($phone, $message, 'disabled');
        }

        try {
            $response = Http::timeout(10)->get($this->baseUrl, [
                'key' => $this->apiKey,
                'destination' => $phone,
                'sender' => $this->sender,
                'content' => $message,
                'urgent' => 'true',
            ]);

            $status = $response->successful() ? 'sent' : 'failed';
            $this->logSms($phone, $message, $status, $response->body());

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('SMS send failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            
            $this->logSms($phone, $message, 'failed', $e->getMessage());
            return false;
        }
    }

    /**
     * Clean and format phone number
     */
    private function cleanPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $clean = preg_replace('/[^0-9]/', '', $phone);

        // Add country code if needed (Georgia = 995)
        if (strlen($clean) === 9) {
            $clean = '995' . $clean;
        }

        return $clean;
    }

    /**
     * Determine target phone for car notification
     */
    private function getTargetPhone(Car $car): ?string
    {
        $owner = $car->owner;

        // If car is owned by admin, send to client phone
        if ($owner?->isAdmin() && $car->client_phone) {
            return $car->client_phone;
        }

        // Otherwise, send to owner if SMS enabled
        if ($owner?->sms_enabled && $owner?->phone) {
            return $owner->phone;
        }

        return null;
    }

    /**
     * Create in-app notification
     */
    private function createNotification(Car $car, string $message): void
    {
        $owner = $car->owner;

        // Don't notify admin about their own cars
        if ($owner && !$owner->isAdmin()) {
            Notification::notify($owner, $car, $message);
        }
    }

    /**
     * Log SMS to database
     */
    private function logSms(string $phone, string $message, string $status, ?string $response = null): bool
    {
        try {
            DB::table('sms_logs')->insert([
                'phone' => $phone,
                'message' => $message,
                'status' => $status,
                'response' => $response ? json_encode(['raw' => $response]) : null,
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('SMS log failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Bulk send SMS (for admin)
     */
    public function sendBulkSms(array $phones, string $message): array
    {
        $results = [];

        foreach ($phones as $phone) {
            $results[$phone] = $this->sendSms($phone, $message);
        }

        return $results;
    }
}
