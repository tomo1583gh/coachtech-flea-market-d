<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1.何も紐餌付いていないユーザー
        if (! User::where('email', 'test@example.com')->exists()) {
            User::create([
                'name' => 'テスト太郎',
                'email' => 'test@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'image_path' => 'avatar/dummy-b.png',
                'zip' => '123-4567',
                'address' => '静岡県静岡市葵区1-1-1',
                'building' => '葵ハイツ101',
            ]);
        }

        // 2.CO01～CO05 を出品するユーザー
        if (! User::where('email', 'seller1@example.com')->exists()) {
            User::create([
                'name' => 'テスト次郎',
                'email' => 'seller1@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'image_path' => 'avatar/dummy-c.png',
                'zip' => '123-4567',
                'address' => '静岡県静岡市駿河区2-2-2',
                'building' => '駿河ハイツ201',
            ]);
        }

        // 3.CO06～CO10を出品するユーザー
        if (! User::where('email', 'seller2@example.com')->exists()) {
            User::create([
                'name' => 'テスト花子',
                'email' => 'seller2@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'image_path' => 'avatar/dummy-r.png',
                'zip' => '123-4567',
                'address' => '静岡県静岡市清水区3-3-3',
                'building' => '清水ハイツ301',
            ]);
        }
    }
}
