<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ip_addresses', function (Blueprint $table): void {
            $table->id();
            $table->ipAddress('ip_address')->unique();
            $table->string('country', 100)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->string('region', 10)->nullable();
            $table->string('region_name', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('zip', 20)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('timezone', 50)->nullable();
            $table->string('isp', 255)->nullable();
            $table->string('org', 255)->nullable();
            $table->string('as', 255)->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamp('geo_updated_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['country', 'city']);
            $table->index('created_by');
            $table->index('geo_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_addresses');
    }
};
