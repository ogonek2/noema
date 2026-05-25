<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->boolean('show_navigator')->default(true);
            $table->boolean('show_footer')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('landing_page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_page_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('admin_label')->nullable();
            $table->json('content')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['landing_page_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_page_sections');
        Schema::dropIfExists('landing_pages');
    }
};
