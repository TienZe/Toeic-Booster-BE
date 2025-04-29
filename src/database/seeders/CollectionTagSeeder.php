<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CollectionTag;

class CollectionTagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['id' => 1, 'tag_name' => 'Grade 5'],
            ['id' => 2, 'tag_name' => 'Grade 6'],
            ['id' => 3, 'tag_name' => 'Grade 7'],
            ['id' => 4, 'tag_name' => 'Grade 8'],
            ['id' => 5, 'tag_name' => 'Grade 9'],
            ['id' => 6, 'tag_name' => 'Grade 10'],
            ['id' => 7, 'tag_name' => 'Grade 11'],
            ['id' => 8, 'tag_name' => 'Grade 12'],
            ['id' => 9, 'tag_name' => 'Events'],
            ['id' => 10, 'tag_name' => 'Test-Prep'],
            ['id' => 11, 'tag_name' => 'Roots & Affixes'],
            ['id' => 12, 'tag_name' => 'Literature'],
            ['id' => 13, 'tag_name' => 'Just for Fun'],
            ['id' => 14, 'tag_name' => 'Non-Fiction'],
        ];

        foreach ($tags as $tag) {
            CollectionTag::updateOrCreate(['id' => $tag['id']], $tag);
        }
    }
}
