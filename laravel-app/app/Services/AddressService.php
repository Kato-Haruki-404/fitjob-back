<?php

namespace App\Services;

use App\Models\Address;
use Illuminate\Support\Facades\Http;

class AddressService
{
    /**
     * 住所文字列を解析して各要素に分割
     */
    public static function parse(string $fullAddress): array
    {
        $result = [
            'prefecture' => '',
            'city' => '',
            'town' => '',
            'address_line' => '',
            'building_name' => '',
        ];
        
        $remaining = $fullAddress;
        
        // 都道府県を抽出（47都道府県のリスト）
        $prefectures = [
            '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
            '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
            '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県',
            '岐阜県', '静岡県', '愛知県', '三重県',
            '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県',
            '鳥取県', '島根県', '岡山県', '広島県', '山口県',
            '徳島県', '香川県', '愛媛県', '高知県',
            '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県',
        ];
        
        foreach ($prefectures as $pref) {
            if (mb_strpos($remaining, $pref) === 0) {
                $result['prefecture'] = $pref;
                $remaining = mb_substr($remaining, mb_strlen($pref));
                break;
            }
        }
        
        // 市区町村を抽出
        // 政令指定都市（〜市〜区）のパターン
        if (preg_match('/^(.+?市.+?区)/u', $remaining, $matches)) {
            $result['city'] = $matches[1];
            $remaining = mb_substr($remaining, mb_strlen($matches[1]));
        }
        // 通常の市町村区
        elseif (preg_match('/^(.+?(?:市|区|町|村))/u', $remaining, $matches)) {
            $result['city'] = $matches[1];
            $remaining = mb_substr($remaining, mb_strlen($matches[1]));
        }
        
        // 町域と番地を分離
        // 「〜丁目」「〜番地」「〜番」などで区切る
        if (preg_match('/^(.+?)([\d０-９]+丁目.*)$/u', $remaining, $matches)) {
            $result['town'] = $matches[1];
            $remaining = $matches[2];
        } elseif (preg_match('/^(.+?)([\d０-９]+番.*)$/u', $remaining, $matches)) {
            $result['town'] = $matches[1];
            $remaining = $matches[2];
        } elseif (preg_match('/^(.+?)([\d０-９]+.*)$/u', $remaining, $matches)) {
            $result['town'] = $matches[1];
            $remaining = $matches[2];
        }
        
        // 建物名を分離（スペースで区切られた部分）
        if (preg_match('/^(.+?)[\s　]+(.+)$/u', $remaining, $matches)) {
            $result['address_line'] = trim($matches[1]);
            $result['building_name'] = trim($matches[2]);
        } else {
            $result['address_line'] = trim($remaining);
        }
        
        return $result;
    }

    /**
     * 国土地理院APIで緯度経度を取得
     */
    public static function getGeocode(string $address): array
    {
        try {
            $response = Http::get('https://msearch.gsi.go.jp/address-search/AddressSearch', [
                'q' => $address,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data) && isset($data[0]['geometry']['coordinates'])) {
                    $coords = $data[0]['geometry']['coordinates'];
                    return [
                        'longitude' => (float) $coords[0],
                        'latitude' => (float) $coords[1],
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Geocoding failed: ' . $e->getMessage());
        }
        
        return ['latitude' => 0.0, 'longitude' => 0.0];
    }

    /**
     * HeartRails Express APIで最寄り駅情報を取得
     */
    public static function getNearestStation(float $latitude, float $longitude): array
    {
        if ($latitude == 0 || $longitude == 0) {
            return [
                'line_name' => '',
                'nearest_station' => '',
                'walking_minutes' => 0,
            ];
        }
        
        try {
            $response = Http::get('https://express.heartrails.com/api/json', [
                'method' => 'getStations',
                'x' => $longitude,
                'y' => $latitude,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['response']['station'][0])) {
                    $station = $data['response']['station'][0];
                    $distanceStr = $station['distance'] ?? '0m';
                    preg_match('/([\d.]+)/', $distanceStr, $matches);
                    $distanceMeters = (float) ($matches[1] ?? 0);
                    $walkingMinutes = (int) ceil($distanceMeters / 67);
                    
                    return [
                        'line_name' => $station['line'] ?? '',
                        'nearest_station' => $station['name'] ?? '',
                        'walking_minutes' => $walkingMinutes,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Station search failed: ' . $e->getMessage());
        }
        
        return [
            'line_name' => '',
            'nearest_station' => '',
            'walking_minutes' => 0,
        ];
    }

    /**
     * 住所文字列から直接Addressを作成（外部API使用）
     */
    public static function createFromFullAddress(
        string $fullAddress,
        ?string $postalCode = null
    ): Address {
        $parsed = self::parse($fullAddress);
        
        // 緯度経度を取得
        $geocode = self::getGeocode($fullAddress);
        
        // 最寄り駅情報を取得
        $stationInfo = self::getNearestStation($geocode['latitude'], $geocode['longitude']);
        
        return Address::create([
            'postal_code' => $postalCode,
            'prefecture' => $parsed['prefecture'],
            'city' => $parsed['city'],
            'town' => $parsed['town'],
            'address_line' => $parsed['address_line'],
            'building_name' => $parsed['building_name'],
            'latitude' => $geocode['latitude'],
            'longitude' => $geocode['longitude'],
            'line_name' => $stationInfo['line_name'],
            'nearest_station' => $stationInfo['nearest_station'],
            'walking_minutes' => $stationInfo['walking_minutes'],
        ]);
    }

    /**
     * 住所情報からAddressレコードを作成（外部API使用）
     */
    public static function create(
        ?string $postalCode,
        string $prefecture,
        string $addressLine,
        string $city = '',
        string $town = '',
        string $buildingName = ''
    ): Address {
        // prefectureとaddressLineからフル住所を組み立て
        $fullAddress = $prefecture . $addressLine;
        
        // addressLineを解析してcity, town, building_nameを抽出
        $parsed = self::parse($fullAddress);
        
        // 緯度経度を取得
        $geocode = self::getGeocode($fullAddress);
        
        // 最寄り駅情報を取得
        $stationInfo = self::getNearestStation($geocode['latitude'], $geocode['longitude']);
        
        return Address::create([
            'postal_code' => $postalCode,
            'prefecture' => $parsed['prefecture'] ?: $prefecture,
            'city' => $parsed['city'] ?: $city,
            'town' => $parsed['town'] ?: $town,
            'address_line' => $parsed['address_line'] ?: $addressLine,
            'building_name' => $parsed['building_name'] ?: $buildingName,
            'latitude' => $geocode['latitude'],
            'longitude' => $geocode['longitude'],
            'line_name' => $stationInfo['line_name'],
            'nearest_station' => $stationInfo['nearest_station'],
            'walking_minutes' => $stationInfo['walking_minutes'],
        ]);
    }
}

