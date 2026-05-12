<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProductModel::query()->create([
            'name' => 'Active Product',
            'description' => 'Visible in public product catalog.',
            'price' => 1000,
            'stock' => 10,
            'status' => 'active',
        ]);

        ProductModel::query()->create([
            'name' => 'Inactive Product',
            'description' => 'Visible only in admin product catalog.',
            'price' => 1500,
            'stock' => 0,
            'status' => 'inactive',
        ]);
    }
}
