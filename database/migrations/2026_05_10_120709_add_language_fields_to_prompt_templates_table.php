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
        Schema::table('prompt_templates', function (Blueprint $table) {
            $table->string('language_code')->nullable()->after('prompt_text');
            $table->string('language_name')->nullable()->after('language_code');
            $table->string('language_readiness')->nullable()->after('language_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prompt_templates', function (Blueprint $table) {
            $table->dropColumn([
                'language_code',
                'language_name',
                'language_readiness',
            ]);
        });
    }
};
