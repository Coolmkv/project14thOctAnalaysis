<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();
        });

        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->text('prompt');
            $table->json('choices');
            $table->unsignedInteger('position');
            $table->timestamps();
            $table->unique(['assessment_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_questions');
        Schema::dropIfExists('assessments');
    }
};