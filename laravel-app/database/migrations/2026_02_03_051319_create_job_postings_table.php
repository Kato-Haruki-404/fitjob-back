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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('address_id')->constrained('addresses')->onDelete('cascade');
            $table->string('title'); //求人タイトル
            $table->string('company_name'); //会社名
            $table->string('email'); //メールアドレス
            $table->string('tel'); //電 話番号
            $table->enum('salary_type', ['時給', '日給']); //給与形態
            $table->integer('wage'); //時給単価
            $table->enum('employment_type', ['パートタイム', 'アルバイト']); //雇用形態
            $table->string('external_link_url'); //外部リンクURL
            $table->string('image'); //画像
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); //公開フラグ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
