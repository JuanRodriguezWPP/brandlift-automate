<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('brandlift_edit_logs') && !Schema::hasColumn('brandlift_edit_logs', 'changes_made')) {
            Schema::table('brandlift_edit_logs', function (Blueprint $table) {
                $table->json('changes_made')->nullable()->after('user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('brandlift_edit_logs') && Schema::hasColumn('brandlift_edit_logs', 'changes_made')) {
            Schema::table('brandlift_edit_logs', function (Blueprint $table) {
                $table->dropColumn('changes_made');
            });
        }
    }
};
