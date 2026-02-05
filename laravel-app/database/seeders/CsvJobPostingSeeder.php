<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobPosting;
use App\Models\Tag;
use App\Models\Momentum;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Support\Facades\DB;

class CsvJobPostingSeeder extends Seeder
{
    /**
     * CSVファイルパス
     */
    private const CSV_PATH = 'database/seeders/csv/dummy_job_data_100.csv';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // タグが存在するか確認
        $allTags = Tag::all();
        if ($allTags->isEmpty()) {
            $this->command->warn('タグが見つかりません。TagSeederを先に実行してください。');
            return;
        }

        $csvPath = base_path(self::CSV_PATH);
        if (!file_exists($csvPath)) {
            $this->command->error('CSVファイルが見つかりません: ' . self::CSV_PATH);
            return;
        }

        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            $this->command->error('CSVファイルを開けませんでした');
            return;
        }

        // ヘッダー行を読み込み
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            $this->command->error('CSVヘッダーが読み込めませんでした');
            return;
        }

        $successCount = 0;
        $errorCount = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                // 空行をスキップ
                if (empty($row[0])) {
                    continue;
                }

                try {
                    $data = $this->parseCsvRow($row, $allTags);
                    $this->createJobPosting($data);
                    $successCount++;
                } catch (\Exception $e) {
                    $this->command->warn("行のインポートに失敗: {$row[0]} - " . $e->getMessage());
                    $errorCount++;
                }
            }

            DB::commit();
            $this->command->info("CsvJobPostingSeeder: {$successCount}件の求人データをインポートしました (エラー: {$errorCount}件)");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('シーダー実行中にエラーが発生しました: ' . $e->getMessage());
        } finally {
            fclose($handle);
        }
    }

    /**
     * CSV行をパースしてデータ配列を作成
     */
    private function parseCsvRow(array $row, $allTags): array
    {
        // New Header order:
        // 0: title, 1: company_name, 2: email, 3: tel, 
        // 4: salary_type, 5: wage, 
        // 6: employment_type, 7: tags, 
        // 8: calorie, 9: steps, 10: exercise_level,
        // 11: full_address, 12: walking_minutes

        $title = $row[0];
        $companyName = $row[1];
        $email = $row[2];
        $tel = $row[3];
        $salaryType = $row[4];
        $wage = (int) $row[5];
        $employmentType = $row[6];
        $tags = $this->parseTags($row[7], $allTags);
        $calorie = (int) $row[8];
        $steps = (int) $row[9];
        $exerciseLevel = (int) ($row[10] ?? 3);
        $fullAddress = $row[11];
        $walkingMinutes = (int) $row[12];

        return [
            'title' => $title,
            'company_name' => $companyName,
            'email' => $email,
            'tel' => $tel,
            'salary_type' => $salaryType,
            'wage' => $wage,
            'employment_type' => $employmentType,
            'tags' => $tags,
            'calorie' => $calorie,
            'steps' => $steps,
            'exercise_level' => $exerciseLevel,
            'full_address' => $fullAddress,
            'walking_minutes' => $walkingMinutes,
        ];
    }

    /**
     * タグ文字列をパース
     */
    private function parseTags(string $tagString, $allTags): array
    {
        // CSV tags might be enclosed in brackets or quotes depending on Python defaults, 
        // but our basic script kept them like "tag1/tag2" ?
        // Actually the script just copied the value. 
        // Original CSV had "タグA/タグB/..."
        
        $tagNames = array_map('trim', explode('/', $tagString));
        $tagIds = [];

        foreach ($tagNames as $name) {
            $tag = $allTags->firstWhere('name', $name);
            if ($tag) {
                $tagIds[] = $tag->id;
            }
        }

        return $tagIds;
    }

    /**
     * 求人タイトルに基づいて画像パスを選択
     */
    private function getJobImage(string $title): string
    {
        $map = [
            'construction.png' => ['工事', '建設', '現場'],
            'supermarket.png' => ['スーパー', '品出し'],
            'event.png' => ['イベント', '設営'],
            'farm.png' => ['農作業', '収穫'],
            'warehouse.png' => ['倉庫', 'ピッキング', '仕分け'],
            'cleaning.png' => ['清掃', '掃除'],
        ];

        foreach ($map as $image => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_strpos($title, $keyword) !== false) {
                    return 'images/demo' . (mt_rand(1, 6) . '.png');
                }
            }
        }

        return 'images/demo' . (mt_rand(1, 6) . '.png');
    }

    /**
     * 求人データを作成
     */
    private function createJobPosting(array $data): void
    {
        // 1. 住所作成 (AddressService利用)
        // AddressService::createFromFullAddress は外部APIを使って緯度経度を取得しますが、
        // 大量データ生成時はAPI制限や時間がかかる可能性があります。
        // ここでは簡易的にパースだけ行い、緯度経度はランダムまたはダミーを入れるか、
        // もしくは AddressService::parse を使って手動で Address::create するほうが安全です。
        
        // 解析のみ実施
        $parsedAddress = AddressService::parse($data['full_address']);
        
        // 緯度経度はダミーで範囲内の値をセット(東京近辺)
        $lat = 35.6895 + (mt_rand(-100, 100) * 0.001);
        $lng = 139.6917 + (mt_rand(-100, 100) * 0.001);

        $address = Address::create([
            'postal_code' => null,
            'prefecture' => $parsedAddress['prefecture'],
            'city' => $parsedAddress['city'],
            'town' => $parsedAddress['town'],
            'address_line' => $parsedAddress['address_line'],
            'building_name' => $parsedAddress['building_name'],
            'latitude' => $lat,
            'longitude' => $lng,
            'line_name' => 'JR山手線', // ダミー
            'nearest_station' => '新宿駅', // ダミー（本来は最寄り駅判定が必要）
            'walking_minutes' => $data['walking_minutes'],
        ]);

        // 2. 求人作成
        $job = JobPosting::create([
            'title' => $data['title'],
            'company_name' => $data['company_name'],
            'email' => $data['email'],
            'tel' => $data['tel'],
            'salary_type' => $data['salary_type'],
            'wage' => $data['wage'],
            'employment_type' => $data['employment_type'],
            'external_link_url' => 'https://jp.indeed.com/',
            'image' => $this->getJobImage($data['title']),
            'status' => 'approved',
            'address_id' => $address->id,
            'created_at' => now()->subDays(mt_rand(0, 30)),
        ]);

        // 3. 運動情報 (Momentum) 作成
        Momentum::create([
            'job_posting_id' => $job->id,
            'calorie' => $data['calorie'],
            'steps' => $data['steps'],
            'exercise_level' => $data['exercise_level'],
        ]);

        // 4. タグ紐付け
        if (!empty($data['tags'])) {
            $job->tags()->attach($data['tags']);
        }
    }
}
