<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_id')->constrained()->cascadeOnDelete();
            $table->string('model_slug')->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->string('subtitle')->nullable();
            $table->string('color_name')->nullable();
            $table->string('color_slug')->nullable();
            $table->string('color_hex', 7)->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 10, 2)->default(100);
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->string('primary_image_path')->nullable();
            $table->text('fit_summary')->nullable();
            $table->longText('fit_details')->nullable();
            $table->text('fabric_summary')->nullable();
            $table->longText('fabric_details')->nullable();
            $table->longText('care_instructions')->nullable();
            $table->text('size_chart_intro')->nullable();
            $table->text('length_guide')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['catalog_id', 'model_slug']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku');
            $table->string('name')->nullable();
            $table->string('size')->nullable();
            $table->string('length')->default('regular');
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'sku']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('path');
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('product_detail_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->text('content')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('size_chart_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('size_label');
            $table->string('bust')->nullable();
            $table->string('waist')->nullable();
            $table->string('hip')->nullable();
            $table->string('inseam')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();
            $table->string('type')->default('alternative');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'related_product_id', 'type']);
        });

        Schema::create('product_customization_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('type')->default('checkbox');
            $table->json('options')->nullable();
            $table->decimal('price_delta', 10, 2)->default(0);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_customization_options');
        Schema::dropIfExists('product_relations');
        Schema::dropIfExists('size_chart_rows');
        Schema::dropIfExists('product_detail_items');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('catalogs');
    }
};
