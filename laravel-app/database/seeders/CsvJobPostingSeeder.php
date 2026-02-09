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
                    return 'demoImages/demo' . mt_rand(1, 7) . '.png';
                }
            }
        }

        return 'demoImages/demo' . mt_rand(1, 7) . '.png';
    }

    /**
     * 市区町村から推定される最寄り駅（簡易マッピング）
     */
    private function determineStation(string $city, string $town): array
    {
        // マッピング定義 (エリア -> [路線, 駅])
        // これは完全ではなく、シードデータの雰囲気を出すためのものです
        $mapping = [
            '新宿' => ['JR山手線', '新宿駅'],
            '渋' => ['JR山手線', '渋谷駅'],
            '港' => ['東京メトロ日比谷線', '六本木駅'],
            '千代田' => ['JR山手線', '東京駅'],
            '中央' => ['東京メトロ銀座線', '銀座駅'],
            '豊島' => ['JR山手線', '池袋駅'],
            '品川' => ['JR山手線', '大崎駅'],
            '大田' => ['JR京浜東北線', '蒲田駅'],
            '世田谷' => ['東急田園都市線', '三軒茶屋駅'],
            '江東' => ['ゆりかもめ', '有明駅'],
            '墨田' => ['JR総武線', '錦糸町駅'],
            '台東' => ['JR山手線', '上野駅'],
            '立川' => ['JR中央線', '立川駅'],
            '八王子' => ['JR中央線', '八王子駅'],
            '武蔵野' => ['JR中央線', '吉祥寺駅'],
            '三鷹' => ['JR中央線', '三鷹駅'],
            '府中' => ['京王線', '府中駅'],
            '調布' => ['京王線', '調布駅'],
            '町田' => ['小田急線', '町田駅'],
            '横浜' => ['JR根岸線', '横浜駅'],
            '川崎' => ['JR京浜東北線', '川崎駅'],
            '千葉' => ['JR総武線', '千葉駅'],
            '船橋' => ['JR総武線', '船橋駅'],
            'さいたま' => ['JR京浜東北線', '大宮駅'],
        ];

        foreach ($mapping as $key => $info) {
            if (mb_strpos($city, $key) !== false || mb_strpos($town, $key) !== false) {
                return [
                    'line_name' => $info[0],
                    'station_name' => $info[1]
                ];
            }
        }

        // マッチしない場合はランダムまたはデフォルト
        $defaults = [
            ['JR山手線', '新宿駅'],
            ['JR山手線', '渋谷駅'],
            ['JR山手線', '池袋駅'], 
            ['JR山手線', '東京駅'],
            ['JR山手線', '品川駅'],
            ['JR中央線', '立川駅'],
            ['JR中央線', '吉祥寺駅'],
        ];
        
        $random = $defaults[array_rand($defaults)];
        return [
            'line_name' => $random[0],
            'station_name' => $random[1]
        ];
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

        // 路線・駅を住所（市区町村）に基づいて決定
        $stationInfo = $this->determineStation($parsedAddress['city'], $parsedAddress['town']);

        $address = Address::create([
            'postal_code' => null,
            'prefecture' => $parsedAddress['prefecture'],
            'city' => $parsedAddress['city'],
            'town' => $parsedAddress['town'],
            'address_line' => $parsedAddress['address_line'],
            'building_name' => $parsedAddress['building_name'],
            'latitude' => $lat,
            'longitude' => $lng,
            'line_name' => $stationInfo['line_name'],
            'nearest_station' => $stationInfo['station_name'],
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
