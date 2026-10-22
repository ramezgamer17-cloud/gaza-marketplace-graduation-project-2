<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_zone_id')->constrained('delivery_zones')->cascadeOnDelete();
            $table->foreignId('vendor_zone_id')->constrained('delivery_zones')->cascadeOnDelete();
            $table->decimal('fee', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['customer_zone_id', 'vendor_zone_id'], 'delivery_rates_pair_unique');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('delivery_zone_id')
                ->nullable()
                ->after('delivery_company_id')
                ->constrained('delivery_zones')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_zone_id');
        });

        Schema::dropIfExists('delivery_rates');
    }
};
