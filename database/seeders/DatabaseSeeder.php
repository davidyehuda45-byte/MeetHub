<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin
        User::updateOrCreate(
            ['email' => 'admin@meethub.id'],
            [
                'name' => 'Admin MeetHub',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
            ]
        );

        // 2. Host
        User::updateOrCreate(
            ['email' => 'host@meethub.id'],
            [
                'name' => 'Budi Santoso (Host)',
                'password' => Hash::make('password123'),
                'role' => 'host',
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
            ]
        );

        // 3. Peserta
        User::updateOrCreate(
            ['email' => 'peserta@meethub.id'],
            [
                'name' => 'Siti Rahma (Peserta)',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
            ]
        );

        // 4. Default Settings
        Setting::set('app_name', 'MeetHub');
        Setting::set('jitsi_domain', 'meet.jit.si');
        Setting::set('allow_guests_default', '1');
        Setting::set('lobby_enabled_default', '0');
        Setting::set('max_file_size_mb', '25');
    }
}
