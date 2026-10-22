<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('delivery_zones')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['parent_id', 'is_active']);
        });

        Schema::create('delivery_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('zone_id')->nullable()->constrained('delivery_zones')->nullOnDelete();
            $table->string('logo')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('delivery_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('delivery_companies')->nullOnDelete();
            $table->string('type', 20)->default('driver')->comment('driver أو manager');
            $table->foreignId('zone_id')->nullable()->constrained('delivery_zones')->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('vehicle_type', 30)->nullable();
            $table->boolean('is_active')->default(false)->comment('بانتظار موافقة الإدارة');
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_profiles');
        Schema::dropIfExists('delivery_companies');
        Schema::dropIfExists('delivery_zones');
    }
};
