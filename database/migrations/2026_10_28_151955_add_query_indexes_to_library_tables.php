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
        Schema::table('downloads', function (Blueprint $table) {
            $table->index(['material_id', 'downloaded_at'], 'downloads_material_downloaded_index');
            $table->index(['user_id', 'downloaded_at'], 'downloads_user_downloaded_index');
            $table->dropIndex('downloads_material_id_index');
            $table->dropIndex('downloads_user_id_index');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->index(['status', 'published_at'], 'materials_status_published_index');
            $table->index(['status', 'category_id', 'title'], 'materials_status_category_title_index');
            $table->dropIndex('materials_status_index');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(
                ['user_id', 'action', 'created_at'],
                'activity_user_action_created_index',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_user_action_created_index');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->index('status', 'materials_status_index');
            $table->dropIndex('materials_status_published_index');
            $table->dropIndex('materials_status_category_title_index');
        });

        Schema::table('downloads', function (Blueprint $table) {
            $table->index('material_id', 'downloads_material_id_index');
            $table->index('user_id', 'downloads_user_id_index');
            $table->dropIndex('downloads_material_downloaded_index');
            $table->dropIndex('downloads_user_downloaded_index');
        });
    }
};
