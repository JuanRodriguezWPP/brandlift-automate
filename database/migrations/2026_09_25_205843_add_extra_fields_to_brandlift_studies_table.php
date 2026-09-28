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
        Schema::table('brandlift_studies', function (Blueprint $table) {
            $table->date('end_date')->nullable();
            $table->string('investment')->nullable();
            $table->string('cm360_site_id')->nullable();
            $table->json('theme_colors')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brandlift_studies', function (Blueprint $table) {
            $table->dropColumn(['end_date', 'investment', 'cm360_site_id', 'theme_colors']);
        });
    }
};
