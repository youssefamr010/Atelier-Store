# Media, data, and backup operations

## Storage architecture

All new uploads use `App\Services\MediaStorageService`. Files are kept out of
the web root and each `media_assets` record stores its disk, object path, MIME
type, byte size, SHA-256 checksum, and image dimensions. URLs are derived from
the configured disk, so the app can move from local storage to S3-compatible
storage without rewriting product data.

For local development, use `MEDIA_DISK=public` and run:

```sh
php artisan storage:link
```

For production, set `MEDIA_DISK=s3`, `AWS_BUCKET`, credentials, region, and
`AWS_URL` (your CDN/custom domain when used). Object storage should have
versioning enabled, block public listing, and only permit writes from the app
identity. Public product images may be served through the CDN.

## Backups

Back up the database and object storage independently. Take a daily encrypted
database backup, retain at least 30 days, enable object versioning, and test a
restore monthly in a non-production environment. Do not store uploaded media
in Git or directly under `public/`.

## Deployment checklist

1. Populate production secrets in the deployment environment; never commit them.
2. Run `php artisan migrate --force` once per release.
3. Run `php artisan storage:link` only for local-disk deployments.
4. Run `php artisan config:cache`, `route:cache`, and `view:cache` after a successful migration.
5. Verify the checkout, payment webhooks, a media upload, and an image delivery URL.

## Existing media

Older media records that have only `url` and `filename` remain supported. New
records populate `disk` and `path`. Migrate legacy files to object storage in a
separate, verified operation; do not delete local originals until delivery and
database records have been checked.
