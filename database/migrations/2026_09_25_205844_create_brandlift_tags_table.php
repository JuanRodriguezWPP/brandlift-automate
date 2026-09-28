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
        Schema::create('brandlift_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brandlift_study_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('success');
            $table->integer('question_number')->nullable();
            $table->string('creative_name')->nullable();
            $table->string('tag_type')->nullable();
            $table->string('placement_id')->nullable();
            $table->text('tag_script')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brandlift_tags');
    }
};
