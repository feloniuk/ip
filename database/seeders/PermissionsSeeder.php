<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Сідер для створення ролей та дозволів системи
 */
class PermissionsSeeder extends Seeder
{
    /**
     * Запуск сідера
     */
    public function run(): void
    {
        // Очищаємо кеш дозволів
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Створюємо дозволи для IP адрес
        $permissions = [
            'view ip addresses',
            'create ip addresses',
            'update ip addresses', 
            'delete ip addresses',
            'export ip addresses', // Для майбутнього функціоналу
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'sanctum');
        }

        // Створюємо ролі
        $adminRole = Role::findOrCreate('admin', 'sanctum');
        $userRole = Role::findOrCreate('user', 'sanctum');

        // Призначаємо дозволи адміну (повний доступ)
        $adminRole->givePermissionTo([
            'view ip addresses',
            'create ip addresses',
            'update ip addresses',
            'delete ip addresses', 
            'export ip addresses',
        ]);

        // Призначаємо дозволи користувачу (тільки перегляд)
        $userRole->givePermissionTo([
            'view ip addresses',
            'export ip addresses',
        ]);

        $this->command->info('Permissions and roles created successfully!');
    }
}