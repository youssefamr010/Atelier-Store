<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Primary Super Admin
        $superAdmin = User::firstOrNew(['email' => 'monoahsec@gmail.com']);
        $superAdmin->name = 'Super Admin (Monoahsec)';
        $superAdmin->is_admin = true;
        $superAdmin->admin_role = 'super_admin';
        if (!$superAdmin->exists || empty($superAdmin->password)) {
            $superAdmin->password = Hash::make('12345678');
        }
        $superAdmin->save();

        // Default Atelier Admin fallback
        $admin = User::firstOrNew(['email' => 'admin@atelier.com']);
        $admin->name = 'Admin';
        $admin->is_admin = true;
        $admin->admin_role = 'super_admin';
        if (!$admin->exists || empty($admin->password)) {
            $admin->password = Hash::make('12345678');
        }
        $admin->save();
    }
}

