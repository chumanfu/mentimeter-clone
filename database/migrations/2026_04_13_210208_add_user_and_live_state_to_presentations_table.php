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
        Schema::table('presentations', function (Blueprint $table) {
            $table->dropColumn('host_name');
        });

        Schema::table('presentations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->boolean('is_live')->default(false)->after('manage_token');
        });

        Schema::table('presentations', function (Blueprint $table) {
            $table->string('join_code', 6)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presentations', function (Blueprint $table) {
            $table->string('join_code', 6)->nullable(false)->change();
        });

        Schema::table('presentations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'is_live']);
        });

        Schema::table('presentations', function (Blueprint $table) {
            $table->string('host_name')->default('');
        });
    }
};
