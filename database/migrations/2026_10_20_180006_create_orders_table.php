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
            $table->string('order_number', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('الزبون');
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete()->comment('البائع');
            $table->foreignId('delivery_user_id')->nullable()->constrained('users')->nullOnDelete()->comment('موظف التوصيل');

            $table->string('status', 20)->default('pending');
            $table->string('payment_method', 30)->default('jawwal_pay');
            $table->string('payment_status', 20)->default('unpaid');
            $table->string('payment_reference')->nullable();

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->string('shipping_address');
            $table->text('notes')->nullable();

            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index(['vendor_id', 'status']);
            $table->index(['delivery_user_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
