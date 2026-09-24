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
        Schema::create('brandlift_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brandlift_study_id')
                  ->constrained('brandlift_studies')
                  ->cascadeOnDelete();
            $table->integer('question_number');
            $table->text('question_text');
            $table->json('answers');
            $table->longText('creative_html')->nullable();
            $table->timestamps();

            $table->index(['brandlift_study_id', 'question_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brandlift_questions');
    }
};
