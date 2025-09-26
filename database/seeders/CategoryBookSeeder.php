<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Book;

class CategoryBookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Categories
        $categories = [];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['name' => $categoryData['name']],
                $categoryData
            );
        }

        // Get created categories
        $fiction = Category::where('name', 'Fiction')->first();
        $nonFiction = Category::where('name', 'Non-Fiction')->first();
        $sciFi = Category::where('name', 'Science Fiction')->first();
        $mystery = Category::where('name', 'Mystery & Thriller')->first();
        $romance = Category::where('name', 'Romance')->first();
        $fantasy = Category::where('name', 'Fantasy')->first();
        $biography = Category::where('name', 'Biography')->first();
        $selfHelp = Category::where('name', 'Self-Help')->first();

        // Create Sample Books
        $books = [];

        foreach ($books as $bookData) {
            Book::firstOrCreate(
                ['title' => $bookData['title']],
                $bookData
            );
        }
    }
}