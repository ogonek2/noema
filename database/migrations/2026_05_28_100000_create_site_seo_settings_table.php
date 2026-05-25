<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_seo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('NOEMA');
            $table->string('title_separator', 8)->default(' | ');
            $table->string('home_meta_title')->nullable();
            $table->text('home_meta_description')->nullable();
            $table->text('home_meta_keywords')->nullable();
            $table->text('default_meta_description')->nullable();
            $table->text('default_meta_keywords')->nullable();
            $table->string('catalog_index_meta_title')->nullable();
            $table->text('catalog_index_meta_description')->nullable();
            $table->text('catalog_index_meta_keywords')->nullable();
            $table->string('robots')->default('index, follow');
            $table->string('og_site_name')->nullable();
            $table->string('og_locale', 12)->default('uk_UA');
            $table->string('og_default_title')->nullable();
            $table->text('og_default_description')->nullable();
            $table->string('og_default_image')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('apple_touch_icon_path')->nullable();
            $table->string('twitter_site')->nullable();
            $table->string('google_site_verification')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_seo_settings');
    }
};
