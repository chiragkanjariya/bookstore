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
        $categories = [
            [
                'name' => 'Fiction',
                'description' => 'Fictional stories and novels',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Non-Fiction',
                'description' => 'Real-world topics and factual content',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Science Fiction',
                'description' => 'Futuristic and scientific fiction',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Mystery & Thriller',
                'description' => 'Suspenseful and mysterious stories',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Romance',
                'description' => 'Love stories and romantic fiction',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Fantasy',
                'description' => 'Magical and fantastical stories',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Biography',
                'description' => 'Life stories of notable people',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Self-Help',
                'description' => 'Personal development and improvement',
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];

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
        $books = [
            // Fiction
            [
                'title' => 'The Great Gatsby',
                'author' => 'F. Scott Fitzgerald',
                'description' => 'A classic American novel set in the Jazz Age, exploring themes of wealth, love, and the American Dream.',
                'price' => 1299.00,
                'shipping_price' => 50.00,
                'stock' => 25,
                'language' => 'English',
                'status' => 'active',
                'category_id' => $fiction->id,
            ],
            [
                'title' => 'To Kill a Mockingbird',
                'author' => 'Harper Lee',
                'description' => 'A gripping tale of racial injustice and childhood innocence in the American South.',
                'price' => 1499.00,
                'shipping_price' => 50.00,
                'stock' => 30,
                'language' => 'English',
                'status' => 'active',
                'category_id' => $fiction->id,
            ],
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'description' => 'A dystopian novel about totalitarianism and surveillance in a future society.',
                'price' => 1199.00,
                'shipping_price' => 50.00,
                'stock' => 20,
                'language' => 'English',
                'status' => 'active',
                'category_id' => $fiction->id,
            ],

            // Science Fiction
            [
                'title' => 'Dune',
                'author' => 'Frank Herbert',
                'description' => 'An epic space opera set on the desert planet Arrakis.',
                'price' => 1799.00,
                'shipping_price' => 75.00,
                'stock' => 15,
                'language' => 'English',
                'status' => 'active',
                'category_id' => $sciFi->id,
            ],
            [
                'title' => 'Foundation',
                'author' => 'Isaac Asimov',
                'description' => 'The first book in the Foundation series about the fall and rise of galactic civilizations.',
                'price' => 1599.00,
                'shipping_price' => 60.00,
                'stock' => 18,
                'language' => 'English',
                'status' => 'active',
                'category_id' => $sciFi->id,
            ],

            // Fantasy
            [
                'title' => 'The Hobbit',
                'author' => 'J.R.R. Tolkien',
                'description' => 'A fantasy adventure about Bilbo Baggins and his unexpected journey.',
                'price' => 1599.00,
                'shipping_price' => 60.00,
                'stock' => 22,
                'language' => 'English',
                'status' => 'active',
                'category_id' => $fantasy->id,
            ],
            [
                'title' => 'Harry Potter and the Philosopher\'s Stone',
                'author' => 'J.K. Rowling',
                'description' => 'The first book in the Harry Potter series about a young wizard\'s adventures.',
                'price' => 1899.00,
                'shipping_price' => 75.00,
                'stock' => 35,
                'language' => 'English',
                'status' => 'active',
                'category_id' => $fantasy->id,
            ],

            // Non-Fiction
            [
                'title' => 'Sapiens',
                'author' => 'Yuval Noah Harari',
                'description' => 'A brief history of humankind from the Stone Age to the present.',
                'price' => 1999.00,
                'shipping_price' => 75.00,
                'stock' => 28,
                'language' => 'English',
                'status' => 'active',
                'category_id' => $nonFiction->id,
            ],
            [
                'title' => 'Educated',
                'author' => 'Tara Westover',
                'description' => 'A memoir about education, family, and the struggle for self-invention.',
                'price' => 1699.00,
                'shipping_price' => 60.00,
                'stock' => 20,
                'language' => 'English',
                'status' => 'active',
                'category_id' => $nonFiction->id,
            ],

            // Mystery & Thriller
            [
                'title' => 'The Da Vinci Code',
                'author' => 'Dan Brown',
                'description' => 'A mystery thriller involving secret societies and religious history.',
                'price' => 1699.00,
                'shipping_price' => 60.00,
                'stock' => 25,
                'language' => 'English',
                'status' => 'active',
                'category_id' => $mystery->id,
            ],
            [
                'title' => 'Gone Girl',
                'author' => 'Gillian Flynn',
                'description' => 'A psychological thriller about a marriage gone terribly wrong.',
                'price' => 1599.00,
                'shipping_price' => 60.00,
                'stock' => 18,
                'language' => 'English',
                'status' => 'active',
                'category_id' => $mystery->id,
            ],

            // Romance
            [
                'title' => 'Pride and Prejudice',
                'author' => 'Jane Austen',
                'description' => 'A classic romance novel about Elizabeth Bennet and Mr. Darcy.',
                'price' => 1099.00,
                'shipping_price' => 50.00,
                'stock' => 30,
                'language' => 'English',
                'status' => 'active',
                'category_id' => $romance->id,
            ],
            [
                'title' => 'The Notebook',
                'author' => 'Nicholas Sparks',
                'description' => 'A touching love story that spans decades.',
                'price' => 1399.00,
                'shipping_price' => 50.00,
                'stock' => 22,
                'language' => 'English',
                'status' => 'active',
                'category_id' => $romance->id,
            ],

            // Self-Help
            [
                'title' => 'The 7 Habits of Highly Effective People',
                'author' => 'Stephen R. Covey',
                'description' => 'A guide to personal and professional effectiveness.',
                'price' => 1799.00,
                'shipping_price' => 60.00,
                'stock' => 25,
                'language' => 'English',
                'status' => 'active',
                'category_id' => $selfHelp->id,
            ],
            [
                'title' => 'Atomic Habits',
                'author' => 'James Clear',
                'description' => 'An easy and proven way to build good habits and break bad ones.',
                'price' => 1999.00,
                'shipping_price' => 75.00,
                'stock' => 30,
                'language' => 'English',
                'status' => 'active',
                'category_id' => $selfHelp->id,
            ],

            // Biography
            [
                'title' => 'Steve Jobs',
                'author' => 'Walter Isaacson',
                'description' => 'The exclusive biography of Apple co-founder Steve Jobs.',
                'price' => 2199.00,
                'shipping_price' => 75.00,
                'stock' => 15,
                'language' => 'English',
                'status' => 'active',
                'category_id' => $biography->id,
            ],
        ];

        foreach ($books as $bookData) {
            Book::firstOrCreate(
                ['title' => $bookData['title']],
                $bookData
            );
        }
    }
}