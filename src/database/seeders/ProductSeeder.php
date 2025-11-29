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
        // UserSeeder で作った2人の出荷者を取得
        // (メールアドレスは UserSeeder と揃えておく)
        $seller1 = User::where('email', 'seller1@example.com')->first();
        $seller2 = User::where('email', 'seller2@example.com')->first();

        // 念のため、いなかったら既存ユーザーで代用
        if (! $seller1) {
            $seller1 = User::first();
        }
        if (! $seller2) {
            $seller2 = User::first() ?? $seller1;
        }

        $products = [
            // CO01　腕時計
            [
                'name' => '腕時計',
                'price' => 15000,
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'image_path' => 'products/Armani-Mens-Clock.jpg',
                'state' => 'new',
                'category_names' => ['ファッション', 'メンズ'],
            ],
            // CO02　HDD
            [
                'name' => 'HDD',
                'price' => 5000,
                'description' => '高速で信頼性の高いハードディスク',
                'image_path' => 'products/HDD-Hard-Disk.jpg',
                'state' => 'good',
                'category_names' => ['家電'],
            ],
            // CO03　玉ねぎ
            [
                'name' => '玉ねぎ3束',
                'price' => 300,
                'description' => '新鮮な玉ねぎ3束セット',
                'image_path' => 'products/iLoveIMG-d.jpg',
                'state' => 'fair',
                'category_names' => ['食品'],
            ],
            // CO04　革靴
            [
                'name' => '革靴',
                'price' => 4000,
                'description' => 'クラシックなデザインの革靴',
                'image_path' => 'products/Leather-Shoes-Product-Photo.jpg',
                'state' => 'poor',
                'category_names' => ['ファッション', 'メンズ'],
            ],
            // CO05　ノートPC
            [
                'name' => 'ノートPC',
                'price' => 45000,
                'description' => '高性能なノートパソコン',
                'image_path' => 'products/Living-Room-Laptop.jpg',
                'state' => 'new',
                'category_names' => ['家電'],
            ],
            // CO06　マイク
            [
                'name' => 'マイク',
                'price' => 8000,
                'description' => '高音質のレコーディング用マイク',
                'image_path' => 'products/Music-Mic-4632231.jpg',
                'state' => 'good',
                'category_names' => ['家電'],
            ],
            // CO07　ショルダーバッグ
            [
                'name' => 'ショルダーバッグ',
                'price' => 3500,
                'description' => 'おしゃれなショルダーバッグ',
                'image_path' => 'products/Purse-fashion-pocket.jpg',
                'state' => 'fair',
                'category_names' => ['ファッション', 'レディース'],
            ],
            // CO08　タンブラー
            [
                'name' => 'タンブラー',
                'price' => 500,
                'description' => '使いやすいタンブラー',
                'image_path' => 'products/Tumbler-souvenir.jpg',
                'state' => 'poor',
                'category_names' => ['キッチン'],
            ],
            // CO09　コーヒーミル
            [
                'name' => 'コーヒーミル',
                'price' => 4000,
                'description' => '手動のコーヒーミル',
                'image_path' => 'products/Waitress-with-Coffee-Grinder.jpg',
                'state' => 'new',
                'category_names' => ['キッチン'],
            ],
            // CO10　メイクセット
            [
                'name' => 'メイクセット',
                'price' => 2500,
                'description' => '便利なメイクアップセット',
                'image_path' => 'products/Going-out-makeup-set.jpg',
                'state' => 'good',
                'category_names' => ['コスメ', 'レディース'],
            ],
        ];

        foreach ($products as $index =>$data) {
            $categoryNames = $data['category_names'];
            unset($data['category_names']);

            // 0～4番目(CO01~CO05) → seller1, 5~9番目(CO06~CO10) → seller2
            $data['user_id'] = $index <=4 ? $seller1->id : $seller2->id;

            $product = Product::create($data);

            // カテゴリ名からIDを取得して紐づけ
            $categoryIds = Category::whereIn('name', $categoryNames)->pluck('id')->toArray();
            $product->categories()->sync($categoryIds);
        }
    }
}
