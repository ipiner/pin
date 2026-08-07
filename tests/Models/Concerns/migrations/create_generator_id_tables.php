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
        Schema::create('timestamp_id', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
        });
        Schema::create('redis_id', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
        });
        Schema::create('snowflake_id', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timestamp_id');
        Schema::dropIfExists('redis_id');
        Schema::dropIfExists('snowflake_id');
    }
};
