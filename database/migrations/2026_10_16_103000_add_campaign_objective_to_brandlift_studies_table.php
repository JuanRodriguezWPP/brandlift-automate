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
            if (!Schema::hasColumn('brandlift_studies', 'campaign_objective')) {
                $table->string('campaign_objective')->nullable()->after('campaign_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brandlift_studies', function (Blueprint $table) {
            if (Schema::hasColumn('brandlift_studies', 'campaign_objective')) {
                $table->dropColumn('campaign_objective');
            }
        });
    }
};
