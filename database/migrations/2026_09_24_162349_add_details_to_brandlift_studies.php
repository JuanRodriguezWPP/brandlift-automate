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
            $table->string('client_name')->nullable()->after('campaign_name');
            $table->json('audiences')->nullable()->after('client_name');
            $table->json('dps_tags')->nullable()->after('audiences');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brandlift_studies', function (Blueprint $table) {
            $table->dropColumn(['client_name', 'audiences', 'dps_tags']);
        });
    }
};
