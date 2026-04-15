<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('choices') || ! Schema::hasTable('questions')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $constraintExists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'choices')
            ->where('CONSTRAINT_NAME', 'choices_question_id_foreign')
            ->exists();

        if (! $constraintExists) {
            Schema::table('choices', function (Blueprint $table) {
                $table->foreign('question_id')
                    ->references('id')
                    ->on('questions')
                    ->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('choices')) {
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $constraintExists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'choices')
            ->where('CONSTRAINT_NAME', 'choices_question_id_foreign')
            ->exists();

        if ($constraintExists) {
            Schema::table('choices', function (Blueprint $table) {
                $table->dropForeign('choices_question_id_foreign');
            });
        }
    }
};
