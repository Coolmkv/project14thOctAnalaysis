<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('assessments')
            ->where('assessment_type', 'riasec')
            ->whereRaw('LOWER(title) NOT LIKE ?', ['%riasec%'])
            ->whereRaw('LOWER(title) NOT LIKE ?', ['%interest inventory%'])
            ->whereRaw('LOWER(description) NOT LIKE ?', ['%riasec%'])
            ->whereRaw('LOWER(description) NOT LIKE ?', ['%interest inventory%'])
            ->update(['assessment_type' => 'general']);
    }

    public function down(): void
    {
        // Existing titles and descriptions are not sufficient to reliably restore the prior type.
    }
};
