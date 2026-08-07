<?php

use Illuminate\Database\Schema\Blueprint;
use Pin\Database\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->schema()->create('admins', function (Blueprint $table) {
            $this->useTable($table);
            $this->id(false);
            $this->string('username', '用户名', 30);
            $this->timestamps();
            $this->deleted();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->schema()->dropIfExists('admins');
    }
};
