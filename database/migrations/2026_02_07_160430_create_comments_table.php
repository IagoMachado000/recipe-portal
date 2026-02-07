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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();
            $table->string('body', 1000);
            $table->timestamp('created_at')->useCurrent();
        });

        DB::statement("
            CREATE INDEX comments_recipe_created_at_idx
            ON comments (recipe_id, created_at DESC)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS comments_recipe_created_at_idx');
        Schema::dropIfExists('comments');
    }
};
