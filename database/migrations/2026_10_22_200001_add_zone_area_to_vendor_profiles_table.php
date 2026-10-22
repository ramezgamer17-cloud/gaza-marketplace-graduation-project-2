<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->foreignId('zone_id')->nullable()->after('address')->constrained('delivery_zones')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->after('zone_id')->constrained('delivery_zones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('area_id');
            $table->dropConstrainedForeignId('zone_id');
        });
    }
};
