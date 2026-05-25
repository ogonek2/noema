<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('telegram_enabled')->default(false);
            $table->text('telegram_bot_token')->nullable();
            $table->string('telegram_chat_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('form_key')->index();
            $table->foreignId('landing_page_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('landing_page_section_id')->nullable();
            $table->string('landing_page_slug')->nullable();
            $table->string('form_title')->nullable();
            $table->json('payload');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referer')->nullable();
            $table->boolean('telegram_sent')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_settings');
    }
};
