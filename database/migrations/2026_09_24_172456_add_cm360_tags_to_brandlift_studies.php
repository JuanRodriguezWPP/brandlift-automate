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
            $table->longText('cm360_tags')->nullable()->after('cm360_campaign_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brandlift_studies', function (Blueprint $table) {
            $table->dropColumn('cm360_tags');
        });
    }
};
