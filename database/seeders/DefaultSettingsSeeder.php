<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class DefaultSettingsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $settings = [
            // Company Settings
            [
                'key' => 'company_name',
                'value' => 'IPDC STORE',
                'type' => 'string',
                'category' => 'company',
                'description' => 'Company Name'
            ],
            [
                'key' => 'company_address',
                'value' => '',
                'type' => 'text',
                'category' => 'company',
                'description' => 'Company Address'
            ],
            [
                'key' => 'company_place',
                'value' => '',
                'type' => 'string',
                'category' => 'company',
                'description' => 'Company Place'
            ],
            [
                'key' => 'company_email',
                'value' => '',
                'type' => 'string',
                'category' => 'company',
                'description' => 'Company Email'
            ],
            [
                'key' => 'company_phone',
                'value' => '',
                'type' => 'string',
                'category' => 'company',
                'description' => 'Company Phone'
            ],
            
            // Payment Settings
            [
                'key' => 'razorpay_key_id',
                'value' => '',
                'type' => 'string',
                'category' => 'payment',
                'description' => 'Razorpay Key ID'
            ],
            [
                'key' => 'razorpay_key_secret',
                'value' => '',
                'type' => 'string',
                'category' => 'payment',
                'description' => 'Razorpay Key Secret'
            ],
            
            // Shipping Settings
            [
                'key' => 'shiprocket_email',
                'value' => '',
                'type' => 'string',
                'category' => 'shipping',
                'description' => 'Shiprocket Email'
            ],
            [
                'key' => 'shiprocket_password',
                'value' => '',
                'type' => 'string',
                'category' => 'shipping',
                'description' => 'Shiprocket Password'
            ]
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'category' => $setting['category'],
                    'description' => $setting['description']
                ]
            );
        }
    }
}