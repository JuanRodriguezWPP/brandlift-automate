<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brandlift_edit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brandlift_study_id')->constrained('brandlift_studies')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('changes_made');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brandlift_edit_logs');
    }
};
