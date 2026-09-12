<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$user = User::updateOrCreate(
    ['email' => 'admin@atelier.eg'],
    [
        'name' => 'Admin',
        'password' => bcrypt('admin123'),
        'is_admin' => true,
        'admin_role' => 'super_admin',
    ]
);

echo "Admin created/updated: " . $user->email . "\n";
echo "Password: admin123\n";
