<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalogs', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('description');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->text('meta_keywords')->nullable()->after('meta_description');
            $table->string('og_image_path')->nullable()->after('meta_keywords');
        });

        Schema::table('landing_pages', function (Blueprint $table) {
            $table->text('meta_keywords')->nullable()->after('meta_description');
            $table->string('og_image_path')->nullable()->after('meta_keywords');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->text('meta_keywords')->nullable()->after('meta_description');
            $table->string('og_image_path')->nullable()->after('meta_keywords');
        });
    }

    public function down(): void
    {
        Schema::table('catalogs', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords', 'og_image_path']);
        });

        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropColumn(['meta_keywords', 'og_image_path']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['meta_keywords', 'og_image_path']);
        });
    }
};
