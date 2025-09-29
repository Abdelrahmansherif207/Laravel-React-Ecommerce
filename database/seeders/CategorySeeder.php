<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Department;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categoriesByDepartment = [
            'Electronics' => [
                'Computers',
                'Mobile Phones',
                'Cameras',
                'Headphones',
                'TV & Home Theater',
            ],
            'Fashion' => [
                'Men\'s Clothing',
                'Women\'s Clothing',
                'Shoes',
                'Bags',
                'Accessories',
            ],
            'Home & Kitchen' => [
                'Furniture',
                'Appliances',
                'Cookware',
                'Decor',
                'Bedding',
            ],
            'Sports & Outdoors' => [
                'Fitness Equipment',
                'Cycling',
                'Camping & Hiking',
                'Team Sports',
                'Water Sports',
            ],
            'Beauty & Personal Care' => [
                'Skincare',
                'Haircare',
                'Makeup',
                'Fragrance',
                'Bath & Body',
            ],
            'Toys & Games' => [
                'Board Games',
                'Action Figures',
                'Educational Toys',
                'Dolls',
                'Outdoor Play',
            ],
        ];

        $allCategories = [];

        foreach ($categoriesByDepartment as $departmentName => $categories) {
            $department = Department::where('slug', Str::slug($departmentName))->first();

            if (!$department) {
                continue; // skip if department not seeded
            }

            foreach ($categories as $category) {
                $allCategories[] = [
                    'name' => $category,
                    'department_id' => $department->id,
                    'parent_id' => null, // all top-level for now
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('categories')->insert($allCategories);
    }
}
