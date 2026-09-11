<?php

declare(strict_types=1);

return [
    /*
     * Keep uploads outside the web root. Set MEDIA_DISK=s3 in production to
     * move the same code path to an S3-compatible object store/CDN.
     */
    'disk' => env('MEDIA_DISK', 'public'),
    'directory' => env('MEDIA_DIRECTORY', 'media'),
    'max_image_size_kb' => (int) env('MEDIA_MAX_IMAGE_SIZE_KB', 5120),
    'max_asset_size_kb' => (int) env('MEDIA_MAX_ASSET_SIZE_KB', 51200),
];
