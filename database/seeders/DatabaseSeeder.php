<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Головний сідер бази даних
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Запуск всіх сідерів
     */
    public function run(): void
    {
        $this->call([
            PermissionsSeeder::class,
        ]);

        // Створюємо тестового адміна
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin');

        // Створюємо тестового користувача
        $user = User::factory()->create([
            'name' => 'Regular User', 
            'email' => 'user@example.com',
        ]);
        $user->assignRole('user');

        $this->command->info('Test users created:');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('User: user@example.com / password');
    }
}