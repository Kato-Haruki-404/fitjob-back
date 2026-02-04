<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            '歩く時間が多い',
            '立ちっぱなし',
            '重いものを持つ',
            '繰り返し動作が多い',
            '屋外作業',
            '屋内作業',
            '上半身',
            '下半身',
            '体幹',
            '全身',
        ];

        foreach ($tags as $name) {
            Tag::firstOrCreate(['name' => $name]);
        }
    }
}
