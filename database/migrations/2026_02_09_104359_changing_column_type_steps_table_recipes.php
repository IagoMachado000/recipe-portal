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
        DB::statement('
            ALTER TABLE recipes
            ALTER COLUMN steps TYPE jsonb
            USING steps::jsonb
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            DB::statement('
                ALTER TABLE recipes
                ALTER COLUMN steps TYPE text
                USING steps::text
            ');
        });
    }
};
