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
        Schema::create('workers', function (Blueprint $table) {
            $table->foreignId('account_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recruitment_id')->constrained('recruitments')->cascadeOnDelete();
            $table->primary(['account_id', 'recruitment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};
