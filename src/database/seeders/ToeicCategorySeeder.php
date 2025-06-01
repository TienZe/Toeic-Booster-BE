<?php

namespace Database\Seeders;

use App\Models\ToeicTestCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ToeicCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 2024
        // 2023
        // 2022
        // 2021
        // 2020
        // 2019
        // New economy
        $categories = [
            [
                'id' => 1,
                'category' => '2024'
            ],
            [
                'id' => 2,
                'category' => '2023'
            ],
            [
                'id' => 3,
                'category' => '2022'
            ],
            [
                'id' => 4,
                'category' => '2021'
            ],
            [
                'id' => 5,
                'category' => '2020'
            ],
            [
                'id' => 6,
                'category' => '2019'
            ],
            [
                'id' => 7,
                'category' => 'New economy'
            ]
        ];

        foreach ($categories as $category) {
            ToeicTestCategory::firstOrCreate($category);
        }
    }
}
