<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class BulkPurchaseSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(
            ['key' => 'min_bulk_purchase'],
            [
                'value' => '10',
                'type' => 'integer',
                'category' => 'bulk',
                'description' => 'Minimum quantity for bulk purchase (free shipping)'
            ]
        );
    }
}