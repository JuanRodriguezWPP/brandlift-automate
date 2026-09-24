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
        Schema::create('brandlift_creatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brandlift_study_id')->constrained('brandlift_studies')->onDelete('cascade');
            $table->integer('question_number');
            $table->string('variant_key')->nullable();
            $table->string('cm360_creative_id')->nullable();
            $table->string('cm360_asset_id')->nullable();
            $table->longText('creative_html')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brandlift_creatives');
    }
};
