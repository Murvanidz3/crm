<?php

namespace Database\Seeders;

use App\Enums\CarStatus;
use App\Enums\UserRole;
use App\Models\SmsTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default admin user
        User::create([
            'username' => 'admin',
            'full_name' => 'Administrator',
            'phone' => '',
            'password' => Hash::make('admin123'),
            'role' => UserRole::ADMIN,
            'balance' => 0,
            'sms_enabled' => true,
        ]);

        // Create SMS templates for each status
        $templates = [
            CarStatus::PURCHASED->value => 'თქვენი [მანქანა] [წელი] წარმატებით შეძენილია აუქციონზე! VIN: [ვინ], ლოტი: [ლოტი]',
            CarStatus::WAREHOUSE->value => 'თქვენი [მანქანა] მივიდა საწყობში და ემზადება ტრანსპორტირებისთვის. VIN: [ვინ]',
            CarStatus::LOADED->value => 'თქვენი [მანქანა] ჩაიტვირთა კონტეინერში [კონტეინერი]. VIN: [ვინ]',
            CarStatus::ON_WAY->value => 'თქვენი [მანქანა] გზაშია საქართველოსკენ! კონტეინერი: [კონტეინერი]. VIN: [ვინ]',
            CarStatus::POTI->value => 'თქვენი [მანქანა] ჩამოვიდა ფოთის პორტში! VIN: [ვინ]',
            CarStatus::GREEN->value => 'თქვენი [მანქანა] გავიდა განბაჟებაზე და მზად არის გასაყვანად! VIN: [ვინ]',
            CarStatus::DELIVERED->value => 'თქვენი [მანქანა] გაყვანილია! გმადლობთ ნდობისთვის! VIN: [ვინ]',
        ];

        foreach ($templates as $status => $text) {
            SmsTemplate::create([
                'status_key' => $status,
                'template_text' => $text,
            ]);
        }
    }
}
