<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ShreeMarutiCourierService;
use App\Services\CourierManager;

class TestShreeMaruti extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:shree-maruti {--test=all : Test to run (all, auth, rates, states)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Shree Maruti Courier API integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $test = $this->option('test');

        $this->info('🚀 Testing Shree Maruti Courier API Integration');
        $this->info('Environment: ' . config('services.shree_maruti.environment'));
        $this->info('Client Code: ' . config('services.shree_maruti.client_code'));
        $this->newLine();

        try {
            $service = new ShreeMarutiCourierService();

            switch ($test) {
                case 'auth':
                    $this->testAuthentication($service);
                    break;

                case 'rates':
                    $this->testRateCalculator($service);
                    break;

                case 'states':
                    $this->testStateMaster($service);
                    break;

                case 'all':
                default:
                    $this->testAuthentication($service);
                    $this->newLine();
                    $this->testStateMaster($service);
                    $this->newLine();
                    $this->testRateCalculator($service);
                    break;
            }

            $this->newLine();
            $this->info('✅ All tests completed!');

        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Test authentication
     */
    private function testAuthentication($service)
    {
        $this->info('📝 Testing Authentication...');

        $token = $service->authenticate();

        if ($token) {
            $this->info('✅ Authentication successful!');
            $this->line('Token: ' . substr($token, 0, 20) . '...');
        } else {
            $this->error('❌ Authentication failed!');
        }
    }

    /**
     * Test state master data
     */
    private function testStateMaster($service)
    {
        $this->info('📝 Testing State Master API...');

        $states = $service->getStateMaster();

        if ($states && is_array($states)) {
            $this->info('✅ State Master API working!');
            $this->line('Total states: ' . count($states));

            if (count($states) > 0) {
                $this->line('Sample states:');
                foreach (array_slice($states, 0, 5) as $state) {
                    $this->line('  - ' . $state['StateName'] . ' (Zone: ' . $state['ZoneName'] . ')');
                }
            }
        } else {
            $this->error('❌ State Master API failed!');
        }
    }

    /**
     * Test rate calculator
     */
    private function testRateCalculator($service)
    {
        $this->info('📝 Testing Rate Calculator API...');

        // Test with sample pincodes
        $fromPincode = '380015'; // Ahmedabad
        $toPincode = '110001';   // Delhi
        $weight = 0.5; // 500 grams

        $this->line("From: $fromPincode, To: $toPincode, Weight: {$weight}kg");

        $rates = $service->getCourierCompanies($fromPincode, $toPincode, $weight);

        if ($rates && isset($rates['success']) && $rates['success'] == '1') {
            $this->info('✅ Rate Calculator API working!');

            if (isset($rates['data']) && is_array($rates['data'])) {
                $this->line('Available rates:');
                foreach ($rates['data'] as $rate) {
                    $this->line(sprintf(
                        '  - %s (%s, %s): ₹%s for %sg',
                        $rate['ServiceType'] ?? 'N/A',
                        $rate['TravelType'] ?? 'N/A',
                        $rate['DocType'] ?? 'N/A',
                        $rate['FreightCharge'] ?? 'N/A',
                        $rate['Weight'] ?? 'N/A'
                    ));
                }
            }
        } else {
            $this->error('❌ Rate Calculator API failed!');
            if (isset($rates['message'])) {
                $this->line('Message: ' . $rates['message']);
            }
        }
    }
}
