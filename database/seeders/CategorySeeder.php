<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Perfumes Nicho',
                'slug' => 'perfumes-nicho',
                'description' => 'Fragancias exclusivas y de alta gama, creadas por perfumistas con ingredientes selectos.',
            ],
            [
                'name' => 'Perfumes de Diseñador',
                'slug' => 'perfumes-disenador',
                'description' => 'Fragancias de marcas reconocidas mundialmente, elegantes y sofisticadas.',
            ],
            [
                'name' => 'Perfumes Árabes',
                'slug' => 'perfumes-arabes',
                'description' => 'Fragancias orientales intensas y duraderas, muchos dump de perfumes nichos y diseñador.',
            ],
        ];

        foreach ($categories as $category) {
            Category::creating($category);
        }
    }
}
