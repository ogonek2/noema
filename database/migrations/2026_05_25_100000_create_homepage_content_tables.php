<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('label');
            $table->json('content')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('homepage_globals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spotlight_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->json('featured_product_ids')->nullable();
            $table->boolean('use_catalog_audience')->default(true);
            $table->timestamps();
        });

        Schema::create('homepage_reviews', function (Blueprint $table) {
            $table->id();
            $table->text('quote');
            $table->string('author_name');
            $table->string('author_role')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('homepage_audience_cards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image_path')->nullable();
            $table->string('href')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('homepage_benefits', function (Blueprint $table) {
            $table->id();
            $table->string('number_label', 16);
            $table->string('title');
            $table->string('text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('homepage_ribbon_images', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('alt_text')->nullable();
            $table->unsignedSmallInteger('width')->default(900);
            $table->unsignedSmallInteger('height')->default(1200);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_ribbon_images');
        Schema::dropIfExists('homepage_benefits');
        Schema::dropIfExists('homepage_audience_cards');
        Schema::dropIfExists('homepage_reviews');
        Schema::dropIfExists('homepage_globals');
        Schema::dropIfExists('homepage_blocks');
    }
};
