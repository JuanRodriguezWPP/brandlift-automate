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
        Schema::create('brandlift_studies', function (Blueprint $table) {
            $table->id();
            $table->string('market', 50);
            $table->string('campaign_name', 255);
            $table->integer('question_count')->default(1);
            $table->integer('creative_width')->default(300);
            $table->integer('creative_height')->default(250);
            $table->string('sheet_id')->nullable();
            $table->string('cm360_campaign_id')->nullable();
            $table->boolean('cm360_pushed')->default(false);
            $table->timestamp('cm360_pushed_at')->nullable();
            $table->string('status', 20)->default('created'); // created, pushed, error
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->index('market');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brandlift_studies');
    }
};
