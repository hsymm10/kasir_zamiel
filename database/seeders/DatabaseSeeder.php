<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Users
        User::create([
            'name' => 'Super Admin',
            'username' => 'admin',
            'email' => 'admin@zamiel.com',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
        ]);

        User::create([
            'name' => 'Kasir Zamiel 1',
            'username' => 'kasir1',
            'email' => 'kasir1@zamiel.com',
            'password' => Hash::make('kasir123'),
            'role' => 'cashier',
        ]);

        // 2. Seed Categories
        $categories = [
            ['name' => 'Makanan', 'slug' => 'makanan'],
            ['name' => 'Minuman', 'slug' => 'minuman'],
            ['name' => 'Bumbu Dapur', 'slug' => 'bumbu-dapur'],
            ['name' => 'Kebutuhan Rumah Tangga', 'slug' => 'kebutuhan-rumah-tangga'],
        ];

        $catModels = [];
        foreach ($categories as $cat) {
            $catModels[$cat['slug']] = Category::create($cat);
        }

        // 3. Seed Products
        // Product 1: Indomie Goreng
        Product::create([
            'category_id' => $catModels['makanan']->id,
            'name' => 'Indomie Goreng',
            'barcode' => '89686013003',
            'price_buy' => 2500,
            'price_sell' => 3000,
            'stock' => 120,
            'expiration_date' => now()->addMonths(6)->toDateString(),
        ]);

        // Product 2: Minyak Goreng Bimoli 1L
        Product::create([
            'category_id' => $catModels['kebutuhan-rumah-tangga']->id,
            'name' => 'Minyak Goreng Bimoli 1L',
            'barcode' => '8992696001016',
            'price_buy' => 15000,
            'price_sell' => 18000,
            'stock' => 45,
            'expiration_date' => now()->addMonths(3)->toDateString(),
        ]);

        // Product 3: Aqua Botol 600ml
        Product::create([
            'category_id' => $catModels['minuman']->id,
            'name' => 'Aqua Botol 600ml',
            'barcode' => '8886008101053',
            'price_buy' => 2800,
            'price_sell' => 3500,
            'stock' => 80,
            'expiration_date' => null, // air mineral tidak kadaluarsa cepat
        ]);

        // Product 4: Gula Pasir Gulaku 1kg
        Product::create([
            'category_id' => $catModels['makanan']->id,
            'name' => 'Gula Pasir Gulaku 1kg',
            'barcode' => '8993206233054',
            'price_buy' => 13500,
            'price_sell' => 16000,
            'stock' => 35,
            'expiration_date' => now()->addYear()->toDateString(),
        ]);

        // Product 5: Susu UHT Ultramilkin (hampir kadaluarsa untuk uji coba alert, misal 5 hari lagi)
        Product::create([
            'category_id' => $catModels['minuman']->id,
            'name' => 'Susu UHT Ultramilk 250ml',
            'barcode' => '8991001110022',
            'price_buy' => 5000,
            'price_sell' => 6500,
            'stock' => 25,
            'expiration_date' => now()->addDays(5)->toDateString(), // HAMPIR KADALUARSA!
        ]);
    }
}
