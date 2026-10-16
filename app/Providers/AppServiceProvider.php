<?php

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        try {
            if (Schema::hasTable('brandlift_studies')) {
                if (! Schema::hasColumn('brandlift_studies', 'campaign_objective')) {
                    Schema::table('brandlift_studies', function (Blueprint $table) {
                        $table->string('campaign_objective')->nullable()->after('campaign_name');
                    });
                }
                if (! Schema::hasColumn('brandlift_studies', 'assigned_users')) {
                    Schema::table('brandlift_studies', function (Blueprint $table) {
                        $table->json('assigned_users')->nullable()->after('created_by');
                    });
                }
            }
        } catch (\Throwable $e) {
            // Ignore schema check errors during early bootstrap
        }
    }
}
