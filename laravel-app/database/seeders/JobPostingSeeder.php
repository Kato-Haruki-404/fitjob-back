<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobPosting;
use App\Models\Tag;
use App\Models\Momentum;

class JobPostingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // タグを作成
        $tags = collect([
            'エンジニア',
            'デザイナー',
            'リモートワーク',
            '未経験OK',
            '高時給',
        ])->map(fn($name) => Tag::firstOrCreate(['name' => $name]));

        // テスト用求人データ（salary/wageが異なる値で作成日時もずらす）
        $jobs = [
            [
                'title' => 'シニアWebエンジニア',
                'company_name' => '株式会社テックジャム',
                'email' => 'senior@techjam.co.jp',
                'tel' => '03-1111-1111',
                'salary' => 600000,
                'wage' => 3000,
                'external_link_url' => 'https://example.com/job/1',
                'image' => 'images/job1.jpg',
                'is_published' => true,
                'momentum' => ['calorie' => 300, 'steps' => 8000, 'exercise_level' => 3],
                'tags' => ['エンジニア', '高時給'],
                'created_at' => now()->subDays(5),
            ],
            [
                'title' => 'ジュニアデザイナー',
                'company_name' => '株式会社クリエイト',
                'email' => 'junior@create.co.jp',
                'tel' => '03-2222-2222',
                'salary' => 280000,
                'wage' => 1200,
                'external_link_url' => 'https://example.com/job/2',
                'image' => 'images/job2.jpg',
                'is_published' => true,
                'momentum' => ['calorie' => 150, 'steps' => 3000, 'exercise_level' => 1],
                'tags' => ['デザイナー', '未経験OK'],
                'created_at' => now()->subDays(10),
            ],
            [
                'title' => 'フルスタックエンジニア',
                'company_name' => '株式会社フィットジョブ',
                'email' => 'full@fitjob.co.jp',
                'tel' => '03-3333-3333',
                'salary' => 500000,
                'wage' => 2500,
                'external_link_url' => 'https://example.com/job/3',
                'image' => 'images/job3.jpg',
                'is_published' => true,
                'momentum' => ['calorie' => 250, 'steps' => 6000, 'exercise_level' => 2],
                'tags' => ['エンジニア', 'リモートワーク'],
                'created_at' => now()->subDays(3),
            ],
            [
                'title' => 'UIデザイナー',
                'company_name' => '株式会社デザインラボ',
                'email' => 'ui@designlab.co.jp',
                'tel' => '03-4444-4444',
                'salary' => 420000,
                'wage' => 2000,
                'external_link_url' => 'https://example.com/job/4',
                'image' => 'images/job4.jpg',
                'is_published' => true,
                'momentum' => ['calorie' => 180, 'steps' => 4000, 'exercise_level' => 2],
                'tags' => ['デザイナー', '高時給'],
                'created_at' => now()->subDays(1),
            ],
            [
                'title' => 'バックエンドエンジニア',
                'company_name' => '株式会社サーバーサイド',
                'email' => 'backend@serverside.co.jp',
                'tel' => '03-5555-5555',
                'salary' => 550000,
                'wage' => 2800,
                'external_link_url' => 'https://example.com/job/5',
                'image' => 'images/job5.jpg',
                'is_published' => true,
                'momentum' => ['calorie' => 200, 'steps' => 5000, 'exercise_level' => 2],
                'tags' => ['エンジニア', 'リモートワーク'],
                'created_at' => now(),
            ],
        ];

        foreach ($jobs as $data) {
            $momentumData = $data['momentum'];
            $tagNames = $data['tags'];
            unset($data['momentum'], $data['tags']);

            $job = JobPosting::create($data);

            // Momentum作成
            Momentum::create([
                'job_posting_id' => $job->id,
                'calorie' => $momentumData['calorie'],
                'steps' => $momentumData['steps'],
                'exercise_level' => $momentumData['exercise_level'],
            ]);

            // タグ紐付け
            $tagIds = $tags->filter(fn($tag) => in_array($tag->name, $tagNames))->pluck('id');
            $job->tags()->attach($tagIds);
        }

        $this->command->info('JobPostingSeeder: 5件の求人データを作成しました');
    }
}
