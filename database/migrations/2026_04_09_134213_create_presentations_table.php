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
        if (Schema::hasTable('presentations')) {
            return;
        }

        Schema::create('presentations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('host_name');
            $table->string('join_code', 6)->unique();
            $table->string('manage_token', 64)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presentations');
    }
};
