<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceableZipcode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceableZipcodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = '/Users/chirag/Downloads/servicable-zips-maruti.csv';

        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found at: {$csvFile}");
            return;
        }

        $this->command->info('Starting import of serviceable zipcodes...');

        // Truncate table before import
        DB::table('serviceable_zipcodes')->truncate();

        $handle = fopen($csvFile, 'r');

        // Skip header row
        fgetcsv($handle);

        $batch = [];
        $batchSize = 1000;
        $totalImported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            // CSV format: pincode,hub,city,state_code,is_serviceable
            if (count($row) >= 5) {
                $batch[] = [
                    'pincode' => trim($row[0]),
                    'hub' => trim($row[1]),
                    'city' => trim($row[2]),
                    'state_code' => trim($row[3]),
                    'is_serviceable' => strtoupper(trim($row[4])),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Insert in batches for performance
                if (count($batch) >= $batchSize) {
                    DB::table('serviceable_zipcodes')->insert($batch);
                    $totalImported += count($batch);
                    $this->command->info("Imported {$totalImported} records...");
                    $batch = [];
                }
            }
        }

        // Insert remaining records
        if (count($batch) > 0) {
            DB::table('serviceable_zipcodes')->insert($batch);
            $totalImported += count($batch);
        }

        fclose($handle);

        $this->command->info("✅ Successfully imported {$totalImported} serviceable zipcodes!");

        Log::info('ServiceableZipcodeSeeder completed', [
            'total_imported' => $totalImported
        ]);
    }
}
