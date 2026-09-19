<?php
$icons = [
    'public/favicon.png',
    'public/favicon-32x32.png',
    'public/favicon-16x16.png',
    'public/apple-touch-icon.png',
    'public/icons/icon-192.png',
    'public/icons/icon-512.png',
];

foreach ($icons as $file) {
    if (!file_exists($file)) {
        echo "MISSING: $file\n";
        continue;
    }
    $info = getimagesize($file);
    if (!$info) {
        echo "INVALID: $file\n";
        continue;
    }
    $kb = round(filesize($file) / 1024, 1);
    echo "OK [{$info[0]}x{$info[1]}] {$kb}KB — $file\n";
}
