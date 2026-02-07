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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();
            $table->string('title', 120);
            $table->string('description', 500)->nullable();
            $table->jsonb('ingredients');
            $table->text('steps');
            $table->decimal('rating_avg', 3, 2);
            $table->unsignedInteger('rating_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('rating_avg');
            $table->index('created_at');
        });

        DB::statement("
            CREATE INDEX recipes_title_lower_idx
            ON recipes (lower(title))
            WHERE deleted_at IS NULL
        ");

        DB::statement("
            CREATE INDEX recipes_rating_avg_active_idx
            ON recipes (rating_avg)
            WHERE deleted_at IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS recipes_rating_avg_active_idx');
        DB::statement('DROP INDEX IF EXISTS recipes_title_lower_idx');

        Schema::dropIfExists('recipes');
    }
};
