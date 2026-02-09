<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobPosting;
use App\Models\Tag;
use App\Models\Momentum;
use App\Models\Address;

class JobPostingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 全タグを取得
        $allTags = Tag::all();
        if ($allTags->isEmpty()) {
            $this->command->warn('タグが見つかりません。TagSeederを先に実行してください。');
            return;
        }

        // 住所候補リスト
        $addresses = [
            [
                'prefecture' => '東京都', 'city' => '渋谷区', 'town' => '恵比寿南', 'address_line' => '1-2-3', 'building_name' => '恵比寿ビル101',
                'latitude' => 35.6467139, 'longitude' => 139.7101033, 'line_name' => 'JR山手線', 'nearest_station' => '恵比寿駅', 'walking_minutes' => 5
            ],
            [
                'prefecture' => '東京都', 'city' => '新宿区', 'town' => '西新宿', 'address_line' => '2-8-1', 'building_name' => '新宿ビル202',
                'latitude' => 35.689634, 'longitude' => 139.692101, 'line_name' => 'JR山手線', 'nearest_station' => '新宿駅', 'walking_minutes' => 10
            ],
            [
                'prefecture' => '東京都', 'city' => '港区', 'town' => '六本木', 'address_line' => '6-10-1', 'building_name' => '六本木ヒルズ',
                'latitude' => 35.660464, 'longitude' => 139.729249, 'line_name' => '東京メトロ日比谷線', 'nearest_station' => '六本木駅', 'walking_minutes' => 3
            ],
            [
                'prefecture' => '大阪府', 'city' => '大阪市北区', 'town' => '大深町', 'address_line' => '3-1', 'building_name' => 'グランフロント大阪',
                'latitude' => 34.704067, 'longitude' => 135.495036, 'line_name' => 'JR大阪環状線', 'nearest_station' => '大阪駅', 'walking_minutes' => 2
            ],
            [
                'prefecture' => '愛知県', 'city' => '名古屋市中村区', 'town' => '名駅', 'address_line' => '1-1-4', 'building_name' => 'JRセントラルタワーズ',
                'latitude' => 35.170915, 'longitude' => 136.881537, 'line_name' => 'JR東海道本線', 'nearest_station' => '名古屋駅', 'walking_minutes' => 1
            ],
            [
                'prefecture' => '福岡県', 'city' => '福岡市博多区', 'town' => '博多駅中央街', 'address_line' => '1-1', 'building_name' => 'アミュプラザ博多',
                'latitude' => 33.589728, 'longitude' => 130.420727, 'line_name' => 'JR鹿児島本線', 'nearest_station' => '博多駅', 'walking_minutes' => 1
            ],
            [
                'prefecture' => '北海道', 'city' => '札幌市中央区', 'town' => '北11条西', 'address_line' => '1', 'building_name' => '札幌駅パセオ',
                'latitude' => 43.068661, 'longitude' => 141.350755, 'line_name' => 'JR函館本線', 'nearest_station' => '札幌駅', 'walking_minutes' => 4
            ],
            [
                'prefecture' => '神奈川県', 'city' => '横浜市西区', 'town' => '高島', 'address_line' => '2-18-1', 'building_name' => 'そごう横浜店',
                'latitude' => 35.465798, 'longitude' => 139.622340, 'line_name' => 'JR東海道本線', 'nearest_station' => '横浜駅', 'walking_minutes' => 5
            ]
        ];

        // ランダム生成用データプール
        $titles = ['荷物運搬スタッフ', '倉庫内軽作業', 'イベント設営スタッフ', '引っ越しアシスタント', 'ポスティング', 'デリバリー配送', '清掃スタッフ', 'ジムインストラクター補助', '建築現場・資材搬入', '警備員（巡回）'];
        $companies = ['アマゾンジャパン合同会社', '株式会社ABC物流', 'イベントプロデュース株式会社', '引越しのサカイ', 'Uber Eats Japan', '株式会社クリーンサービス', 'スポーツクラブFit', '建設サポート株式会社', 'セキュリティーガード株式会社'];
        $images = [
            'demoImages/demo1.png',
            'demoImages/demo2.png',
            'demoImages/demo3.png',
            'demoImages/demo4.png',
            'demoImages/demo5.png',
            'demoImages/demo6.png',
            'demoImages/demo7.png',
        ];

        // 20件の求人を作成
        for ($i = 0; $i < 20; $i++) {
            // 1. 住所作成
            $randomAddress = $addresses[array_rand($addresses)];
            $address = Address::create([
                'prefecture' => $randomAddress['prefecture'],
                'city' => $randomAddress['city'],
                'town' => $randomAddress['town'],
                'address_line' => $randomAddress['address_line'],
                'building_name' => $randomAddress['building_name'],
                'latitude' => $randomAddress['latitude'] + (mt_rand(-100, 100) * 0.0001), // 少し座標をずらす
                'longitude' => $randomAddress['longitude'] + (mt_rand(-100, 100) * 0.0001),
                'line_name' => $randomAddress['line_name'],
                'nearest_station' => $randomAddress['nearest_station'],
                'walking_minutes' => mt_rand(1, 15),
            ]);

            // 2. 求人作成
            $job = JobPosting::create([
                'title' => $titles[array_rand($titles)],
                'company_name' => $companies[array_rand($companies)],
                'email' => 'test_user_' . $i . '@example.com',
                'tel' => '03-' . mt_rand(1000, 9999) . '-' . mt_rand(1000, 9999),
                'salary_type' => mt_rand(0, 1) ? '時給' : '日給',
                'wage' => mt_rand(10, 30) * 100, // 1000 ~ 3000円
                'employment_type' => mt_rand(0, 1) ? 'アルバイト' : 'パートタイム',
                'external_link_url' => 'https://vantan.jp//' . $i,
                'image' => $images[array_rand($images)],
                'status' => 'approved',
                'address_id' => $address->id,
                'created_at' => now()->subDays(mt_rand(0, 30)), // 過去30日以内のランダムな日時
            ]);

            // 3. 運動情報 (Momentum) 作成
            $exerciseLevel = mt_rand(1, 5);
            Momentum::create([
                'job_posting_id' => $job->id,
                'calorie' => mt_rand(10, 50) * 10, // 100 ~ 500 kcal
                'steps' => mt_rand(20, 150) * 100, // 2000 ~ 15000 steps
                'exercise_level' => $exerciseLevel,
            ]);

            // 4. タグ紐付け (ランダムに1〜4個)
            $randomTags = $allTags->random(mt_rand(1, 4));
            $job->tags()->attach($randomTags->pluck('id'));
        }

        $this->command->info('JobPostingSeeder: 20件のランダムな求人データを作成しました (タグ・住所・運動強度含む)');
    }
}
