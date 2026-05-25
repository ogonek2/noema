<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('internal_notes')->nullable()->after('customer_notes');
            $table->foreignId('assigned_to')->nullable()->after('internal_notes')->constrained('users')->nullOnDelete();
            $table->string('ttn_number', 32)->nullable()->index()->after('assigned_to');
            $table->string('ttn_ref', 64)->nullable()->after('ttn_number');
            $table->string('ttn_status', 32)->nullable()->after('ttn_ref');
            $table->decimal('shipment_weight', 8, 2)->nullable()->after('ttn_status');
            $table->unsignedSmallInteger('shipment_seats')->default(1)->after('shipment_weight');
            $table->timestamp('paid_at')->nullable()->after('shipment_seats');
            $table->timestamp('shipped_at')->nullable()->after('paid_at');
            $table->timestamp('completed_at')->nullable()->after('shipped_at');
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');
        });

        Schema::create('order_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 48);
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32)->nullable();
            $table->text('body')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'created_at']);
        });

        Schema::create('nova_poshta_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->text('api_key')->nullable();
            $table->string('api_url')->default('https://api.novaposhta.ua/v2.0/json/');
            $table->boolean('verify_ssl')->default(true);
            $table->unsignedSmallInteger('timeout')->default(20);

            $table->string('sender_ref', 64)->nullable();
            $table->string('contact_sender_ref', 64)->nullable();
            $table->string('city_sender_ref', 64)->nullable();
            $table->string('sender_address_ref', 64)->nullable();
            $table->string('sender_phone', 32)->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_warehouse_name')->nullable();

            $table->decimal('default_weight', 8, 2)->default(1);
            $table->unsignedSmallInteger('default_seats')->default(1);
            $table->string('default_description')->default('Товар NOEMA');
            $table->string('cargo_type', 32)->default('Cargo');
            $table->string('payment_method', 32)->default('NonCash');
            $table->string('payer_type', 32)->default('Recipient');

            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('checkout_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('liqpay_enabled')->default(false);
            $table->text('liqpay_public_key')->nullable();
            $table->text('liqpay_private_key')->nullable();
            $table->boolean('liqpay_sandbox')->default(true);
            $table->string('liqpay_currency', 8)->default('UAH');
            $table->boolean('cod_enabled')->default(true);
            $table->boolean('iban_enabled')->default(true);
            $table->string('iban_holder')->nullable();
            $table->string('iban_number')->nullable();
            $table->string('iban_bank')->nullable();
            $table->string('iban_purpose')->nullable();
            $table->decimal('default_shipping_cost', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_settings');
        Schema::dropIfExists('nova_poshta_settings');
        Schema::dropIfExists('order_events');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn([
                'internal_notes',
                'assigned_to',
                'ttn_number',
                'ttn_ref',
                'ttn_status',
                'shipment_weight',
                'shipment_seats',
                'paid_at',
                'shipped_at',
                'completed_at',
                'cancelled_at',
            ]);
        });
    }
};
