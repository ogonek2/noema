<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('size_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('size_chart_intro')->nullable();
            $table->text('length_guide')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('size_preset_chart_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('size_preset_id')->constrained()->cascadeOnDelete();
            $table->string('size_label');
            $table->string('bust')->nullable();
            $table->string('waist')->nullable();
            $table->string('hip')->nullable();
            $table->string('inseam')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('size_preset_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('size_preset_id')->constrained()->cascadeOnDelete();
            $table->string('size');
            $table->string('length')->default('regular');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('size_preset_id')
                ->nullable()
                ->after('length_guide')
                ->constrained('size_presets')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('size_preset_id');
        });

        Schema::dropIfExists('size_preset_variants');
        Schema::dropIfExists('size_preset_chart_rows');
        Schema::dropIfExists('size_presets');
    }
};
