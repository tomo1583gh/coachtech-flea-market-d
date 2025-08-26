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
        if (! User::where('email', 'test@example.com')->exists()) {
            user::create([
                'name' => 'テストユーザー',
                'email' => 'test@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'image_path' => 'avatar/sample.png',
                'zip' => '123-4567',
                'address' => '静岡県静岡市葵区1-1-1',
                'building' => '静岡ハイツ201',
            ]);
        }
    }
}
