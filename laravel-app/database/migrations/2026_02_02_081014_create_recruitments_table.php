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
        Schema::create('recruitments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete(); // 募集主
            $table->foreignId('address_id')->constrained('addresses')->cascadeOnDelete(); // 勤務地
            $table->foreignId('thumbnail_id')->constrained('file_paths')->cascadeOnDelete(); //サムネイル画像
            $table->string('title'); // 募集タイトル
            $table->text('work_content'); // 仕事内容
            $table->text('atmosphere_description'); // 職場の雰囲気
            $table->text('welfare_description'); // 福利厚生
            $table->text('appeal_points'); // アピールポイント
            $table->enum('employment_type', ['短期','単発']); // 雇用形態
            $table->text('ideal_candidate'); // 求める人物像
            $table->text('precautions'); // 注意事項
            $table->integer('break_time_minutes')->comment('分単位'); // 休憩時間
            $table->integer('hourly_wage'); // 時給
            $table->integer('daily_wage'); // 日給
            $table->integer('calories_burned'); // 消費カロリー
            $table->enum('motion_level', ['1','2','3','4','5']); // 運動レベル
            $table->integer('capacity')->nullable(); // 定員
            $table->boolean('is_template'); // テンプレートかどうか
            $table->boolean('is_published'); // 公開かどうか
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitments');
    }
};
