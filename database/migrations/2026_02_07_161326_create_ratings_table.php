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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();
            $table->smallInteger('score');
            $table->unique(['recipe_id', 'user_id'], 'ratings_recipe_user_unique');
            $table->timestamp('created_at')->useCurrent();
        });

        DB::statement("
            ALTER TABLE ratings
            ADD CONSTRAINT ratings_score_check
            CHECK (score BETWEEN 1 AND 5)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
