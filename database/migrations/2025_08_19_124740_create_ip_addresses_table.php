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
            
            // Основні поля IP адреси
            $table->ipAddress('ip_address')
                  ->unique()
                  ->comment('Унікальна IP адреса');
            
            // Гео дані
            $table->string('country', 100)->nullable()->comment('Назва країни');
            $table->char('country_code', 2)->nullable()->comment('Код країни (ISO 3166-1 alpha-2)');
            $table->string('region', 100)->nullable()->comment('Код регіону');
            $table->string('region_name', 100)->nullable()->comment('Назва регіону');
            $table->string('city', 100)->nullable()->comment('Назва міста');
            $table->string('zip', 20)->nullable()->comment('Поштовий індекс');
            
            // Координати
            $table->decimal('latitude', 10, 8)->nullable()->comment('Широта (-90 до 90)');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Довгота (-180 до 180)');
            
            // Мережева інформація
            $table->string('timezone', 50)->nullable()->comment('Часовий пояс');
            $table->string('isp')->nullable()->comment('Інтернет провайдер');
            $table->string('org')->nullable()->comment('Організація');
            $table->string('as')->nullable()->comment('Автономна система');
            
            // Метадані
            $table->json('raw_response')->nullable()->comment('Повна відповідь від геолокаційного API');
            $table->timestamp('geo_updated_at')->nullable()->comment('Останнє оновлення геоданих');
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('ID користувача, який створив запис');
            
            $table->timestamps();
            
            // Індекси для оптимізації запитів
            $table->index(['country', 'city'], 'idx_country_city');
            $table->index('created_by', 'idx_created_by');
            $table->index('geo_updated_at', 'idx_geo_updated_at');
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
