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
        Schema::create('momenta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->unique()->constrained('job_postings')->onDelete('cascade');
            $table->integer('calorie'); //カロリー
            $table->integer('steps'); //歩数
            $table->integer('exercise_level'); //運動レベル
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('momenta');
    }
};
