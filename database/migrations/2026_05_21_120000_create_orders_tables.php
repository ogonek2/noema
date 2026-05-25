<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 32)->unique();
            $table->string('status', 32)->index();
            $table->string('payment_status', 32)->index();
            $table->string('payment_method', 32)->nullable();
            $table->string('liqpay_payment_id')->nullable()->index();

            $table->string('customer_name');
            $table->string('customer_phone', 32);
            $table->string('customer_email')->nullable();

            $table->string('shipping_method', 48);
            $table->string('shipping_city_ref', 64)->nullable();
            $table->string('shipping_city_name')->nullable();
            $table->string('shipping_warehouse_ref', 64)->nullable();
            $table->string('shipping_warehouse_name')->nullable();
            $table->text('shipping_address')->nullable();

            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('total', 12, 2);

            $table->text('customer_notes')->nullable();
            $table->string('session_id', 64)->nullable()->index();

            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('product_name');
            $table->string('product_slug')->nullable();
            $table->string('color_name')->nullable();
            $table->string('size')->nullable();
            $table->string('sku')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->json('customizations')->nullable();
            $table->text('notes')->nullable();
            $table->string('image')->nullable();
            $table->string('group_id', 64)->nullable();
            $table->unsignedSmallInteger('group_index')->nullable();
            $table->unsignedSmallInteger('group_total')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
