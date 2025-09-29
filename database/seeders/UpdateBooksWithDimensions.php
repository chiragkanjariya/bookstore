<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;

class UpdateBooksWithDimensions extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing books with default dimensions if they don't have them
        Book::whereNull('height')
            ->orWhereNull('width')
            ->orWhereNull('depth')
            ->orWhereNull('weight')
            ->update([
                'height' => 5.0,  // Default height in cm
                'width' => 15.0,  // Default width in cm
                'depth' => 2.0,   // Default depth in cm
                'weight' => 0.5,  // Default weight in kg
            ]);

        $this->command->info('Updated existing books with default dimensions and weight.');
    }
}