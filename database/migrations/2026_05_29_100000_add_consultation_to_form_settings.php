<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_settings', function (Blueprint $table) {
            $table->boolean('consultation_enabled')->default(true)->after('notes');
            $table->string('consultation_form_key')->default('consultation')->after('consultation_enabled');
            $table->string('consultation_title')->nullable()->after('consultation_form_key');
            $table->text('consultation_subtitle')->nullable()->after('consultation_title');
            $table->text('consultation_success_message')->nullable()->after('consultation_subtitle');
            $table->json('consultation_fields')->nullable()->after('consultation_success_message');
        });
    }

    public function down(): void
    {
        Schema::table('form_settings', function (Blueprint $table) {
            $table->dropColumn([
                'consultation_enabled',
                'consultation_form_key',
                'consultation_title',
                'consultation_subtitle',
                'consultation_success_message',
                'consultation_fields',
            ]);
        });
    }
};
