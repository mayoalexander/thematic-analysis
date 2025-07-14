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
        Schema::create('project_contexts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('reasoning_context')->nullable();
            $table->json('working_context')->nullable();
            $table->json('draft_context')->nullable();
            $table->json('analysis_results')->nullable();
            $table->json('final_draft')->nullable();
            $table->json('progress')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_contexts');
    }
};
