<?php
/**
 * ATELIER — Deploy Hook
 * ─────────────────────────────────────────────────────────────────
 * This file is called by GitHub Actions after every push to main.
 * It bootstraps Laravel and clears all caches automatically.
 *
 * URL: https://atelier404.store/_deploy.php?token=YOUR_TOKEN
 * ─────────────────────────────────────────────────────────────────
 */

// ── SECURITY ────────────────────────────────────────────────────
$expectedToken = getenv('DEPLOY_TOKEN') ?: 'atelier_deploy_secret_2024';

if (($_GET['token'] ?? '') !== $expectedToken) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}

$log   = [];
$start = microtime(true);

// ── GIT PULL ────────────────────────────────────────────────────
$projectDir = dirname(__DIR__);

// Try multiple common git paths (PHP exec PATH is restricted on most servers)
$gitPaths = ['/usr/bin/git', '/usr/local/bin/git', '/bin/git', 'git'];
$gitBin   = null;
foreach ($gitPaths as $path) {
    exec("$path --version 2>&1", $checkOut, $checkCode);
    if ($checkCode === 0) { $gitBin = $path; break; }
}

if ($gitBin) {
    exec("cd " . escapeshellarg($projectDir) . " && $gitBin pull origin main 2>&1", $gitOutput, $gitCode);
    $log['git'] = ['exit_code' => $gitCode, 'output' => implode("\n", $gitOutput)];
} else {
    // git not found in PATH — server likely uses its own cron/webhook deploy mechanism
    // (e.g., scripts/poll-deploy.sh) — cache clearing still succeeds
    $log['git'] = ['exit_code' => 127, 'output' => 'git binary not found in PHP PATH — using server cron deploy'];
}

// ── BOOTSTRAP LARAVEL ───────────────────────────────────────────
try {
    require $projectDir . '/vendor/autoload.php';
    $app = require_once $projectDir . '/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    // Clear all caches
    $caches = ['view:clear', 'config:clear', 'cache:clear', 'route:clear'];
    foreach ($caches as $cmd) {
        \Illuminate\Support\Facades\Artisan::call($cmd);
        $log['artisan'][$cmd] = trim(\Illuminate\Support\Facades\Artisan::output()) ?: 'OK';
    }

    $log['status']  = 'success';
} catch (\Throwable $e) {
    $log['status']  = 'error';
    $log['error']   = $e->getMessage();
}

$log['duration_ms'] = round((microtime(true) - $start) * 1000);
$log['timestamp']   = date('Y-m-d H:i:s');

header('Content-Type: application/json');
echo json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
