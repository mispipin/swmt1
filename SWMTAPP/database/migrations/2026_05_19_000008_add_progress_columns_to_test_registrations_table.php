<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_registrations', function (Blueprint $table) {
            $table->unsignedSmallInteger('progress_current_section')->nullable()->after('tested_at');
            $table->unsignedSmallInteger('progress_current_slide')->nullable()->after('progress_current_section');
            $table->string('progress_ui_stage', 20)->nullable()->after('progress_current_slide');
            $table->json('progress_picked_order')->nullable()->after('progress_ui_stage');
            $table->json('progress_section_results')->nullable()->after('progress_picked_order');
            $table->json('progress_sections')->nullable()->after('progress_section_results');
            $table->timestamp('progress_updated_at')->nullable()->after('progress_sections');
        });
    }

    public function down(): void
    {
        Schema::table('test_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'progress_current_section',
                'progress_current_slide',
                'progress_ui_stage',
                'progress_picked_order',
                'progress_section_results',
                'progress_sections',
                'progress_updated_at',
            ]);
        });
    }
};
