<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\State;
use App\Models\District;
use App\Models\Taluka;
use Illuminate\Support\Facades\DB;

class LocationDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Taluka::truncate();
        District::truncate();
        State::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Path to your CSV file
        $csvFile = database_path('seeders/location_data.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found at: ' . $csvFile);
            $this->command->info('Please place your CSV file at: database/seeders/location_data.csv');
            return;
        }

        $this->command->info('Importing location data from CSV...');

        $handle = fopen($csvFile, 'r');
        if (!$handle) {
            $this->command->error('Could not open CSV file');
            return;
        }

        // Skip header row
        $header = fgetcsv($handle);
        $this->command->info('CSV Header: ' . implode(', ', $header));

        $states = [];
        $districts = [];
        $talukas = [];
        $stateCounter = 1;
        $districtCounter = 1;
        $talukaCounter = 1;

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) < 4) {
                continue; // Skip incomplete rows
            }

            $sr = trim($data[0]);
            $stateName = trim($data[1]);
            $districtName = trim($data[2]);
            $talukaName = trim($data[3]);

            // Skip if any field is empty
            if (empty($stateName) || empty($districtName) || empty($talukaName)) {
                continue;
            }

            // Create state if not exists
            if (!isset($states[$stateName])) {
                $states[$stateName] = [
                    'id' => $stateCounter,
                    'name' => $stateName,
                    'code' => 'ST' . str_pad($stateCounter, 3, '0', STR_PAD_LEFT),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $stateCounter++;
            }

            // Create district if not exists
            $districtKey = $stateName . '_' . $districtName;
            if (!isset($districts[$districtKey])) {
                $districts[$districtKey] = [
                    'id' => $districtCounter,
                    'name' => $districtName,
                    'code' => 'DT' . str_pad($districtCounter, 3, '0', STR_PAD_LEFT),
                    'state_id' => $states[$stateName]['id'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $districtCounter++;
            }

            // Create taluka
            $talukaKey = $districtKey . '_' . $talukaName;
            if (!isset($talukas[$talukaKey])) {
                $talukas[$talukaKey] = [
                    'id' => $talukaCounter,
                    'name' => $talukaName,
                    'code' => 'TK' . str_pad($talukaCounter, 3, '0', STR_PAD_LEFT),
                    'district_id' => $districts[$districtKey]['id'],
                    'state_id' => $states[$stateName]['id'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $talukaCounter++;
            }
        }

        fclose($handle);

        // Insert data in batches
        $this->command->info('Inserting ' . count($states) . ' states...');
        State::insert(array_values($states));

        $this->command->info('Inserting ' . count($districts) . ' districts...');
        District::insert(array_values($districts));

        $this->command->info('Inserting ' . count($talukas) . ' talukas...');
        Taluka::insert(array_values($talukas));

        $this->command->info('Location data imported successfully!');
        $this->command->info('States: ' . count($states));
        $this->command->info('Districts: ' . count($districts));
        $this->command->info('Talukas: ' . count($talukas));
    }
}