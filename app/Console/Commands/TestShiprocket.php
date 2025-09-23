<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ShiprocketService;

class TestShiprocket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shiprocket:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Shiprocket API connection and authentication';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Shiprocket API connection...');
        
        try {
            $shiprocketService = new ShiprocketService();
            $token = $shiprocketService->authenticate();
            
            if ($token) {
                $this->info('✅ Shiprocket authentication successful!');
                $this->info('Token: ' . substr($token, 0, 20) . '...');
                
                // Test getting courier companies
                $this->info('Testing courier companies API...');
                $couriers = $shiprocketService->getCourierCompanies('400001', '110001', 0.5);
                
                if ($couriers) {
                    $this->info('✅ Courier companies API working!');
                    if (isset($couriers['data']) && is_array($couriers['data'])) {
                        $this->info('Available couriers: ' . count($couriers['data']));
                    }
                } else {
                    $this->warn('⚠️ Courier companies API returned empty response');
                }
                
            } else {
                $this->error('❌ Shiprocket authentication failed!');
                $this->error('Please check your credentials in the .env file');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error testing Shiprocket: ' . $e->getMessage());
        }
    }
}
