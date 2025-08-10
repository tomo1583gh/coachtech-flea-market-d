<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        $products = [
            [
                'user_id' => $user->id,
                'name' => '腕時計',
                'price' => 15000,
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'image_path' => 'products/Armani-Mens-Clock.jpg',
                'state' => 'new',
                'category_names' => ['ファッション', 'メンズ'],
            ],
            [
                'user_id' => $user->id,
                'name' => 'HDD',
                'price' => 5000,
                'description' => '高速で信頼性の高いハードディスク',
                'image_path' => 'products/HDD-Hard-Disk.jpg',
                'state' => 'good',
                'category_names' => ['家電'],
            ],
            [
                'user_id' => $user->id,
                'name' => '玉ねぎ3束',
                'price' => 300,
                'description' => '新鮮な玉ねぎ3束セット',
                'image_path' => 'products/iLoveIMG-d.jpg',
                'state' => 'fair',
                'category_names' => ['食品'],
            ],
            [
                'user_id' => $user->id,
                'name' => '革靴',
                'price' => 4000,
                'description' => 'クラシックなデザインの革靴',
                'image_path' => 'products/Leather-Shoes-Product-Photo.jpg',
                'state' => 'poor',
                'category_names' => ['ファッション', 'メンズ'],
            ],
            [
                'user_id' => $user->id,
                'name' => 'ノートPC',
                'price' => 45000,
                'description' => '高性能なノートパソコン',
                'image_path' => 'products/Living-Room-Laptop.jpg',
                'state' => 'new',
                'category_names' => ['家電'],
            ],
            [
                'user_id' => $user->id,
                'name' => 'マイク',
                'price' => 8000,
                'description' => '高音質のレコーディング用マイク',
                'image_path' => 'products/Music-Mic-4632231.jpg',
                'state' => 'good',
                'category_names' => ['家電'],
            ],
            [
                'user_id' => $user->id,
                'name' => 'ショルダーバッグ',
                'price' => 3500,
                'description' => 'おしゃれなショルダーバッグ',
                'image_path' => 'products/Purse-fashion-pocket.jpg',
                'state' => 'fair',
                'category_names' => ['ファッション', 'レディース'],
            ],
            [
                'user_id' => $user->id,
                'name' => 'タンブラー',
                'price' => 500,
                'description' => '使いやすいタンブラー',
                'image_path' => 'products/Tumbler-souvenir.jpg',
                'state' => 'poor',
                'category_names' => ['キッチン'],
            ],
            [
                'user_id' => $user->id,
                'name' => 'コーヒーミル',
                'price' => 4000,
                'description' => '手動のコーヒーミル',
                'image_path' => 'products/Waitress-with-Coffee-Grinder.jpg',
                'state' => 'new',
                'category_names' => ['キッチン'],
            ],
            [
                'user_id' => $user->id,
                'name' => 'メイクセット',
                'price' => 2500,
                'description' => '便利なメイクアップセット',
                'image_path' => 'products/Going-out-makeup-set.jpg',
                'state' => 'good',
                'category_names' => ['コスメ', 'レディース'],
            ],
        ];

        foreach ($products as $data) {
            $categoryNames = $data['category_names'];
            unset($data['category_names']);

            $product = Product::create($data);

            // カテゴリ名からIDを取得して紐づけ
            $categoryIds = Category::whereIn('name', $categoryNames)->pluck('id')->toArray();
            $product->categories()->sync($categoryIds);
        }
    }
}
