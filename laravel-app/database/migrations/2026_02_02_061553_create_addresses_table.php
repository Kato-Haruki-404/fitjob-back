<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('postal_code')->nullable(); // 郵便番号
            $table->string('prefecture'); // 都道府県
            $table->string('city'); // 市区町村
            $table->string('town'); // 町域
            $table->string('address_line'); // 番地
            $table->string('building_name'); // 建物名
            $table->decimal('latitude', 10, 8); // 緯度
            $table->decimal('longitude', 11, 8); // 経度
            $table->string('line_name'); // 路線名
            $table->string('nearest_station'); // 最寄り駅
            $table->integer('walking_minutes'); // 徒歩分数
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
