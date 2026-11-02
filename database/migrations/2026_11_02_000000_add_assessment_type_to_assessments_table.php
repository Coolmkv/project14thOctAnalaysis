<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->string('assessment_type', 20)->default('general')->after('description');
        });

        DB::table('assessments')
            ->where(function ($query) {
                $query->whereRaw('LOWER(title) LIKE ?', ['%riasec%'])
                    ->orWhereRaw('LOWER(title) LIKE ?', ['%interest inventory%'])
                    ->orWhereRaw('LOWER(description) LIKE ?', ['%riasec%'])
                    ->orWhereRaw('LOWER(description) LIKE ?', ['%interest inventory%']);
            })
            ->update(['assessment_type' => 'riasec']);
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn('assessment_type');
        });
    }
};
