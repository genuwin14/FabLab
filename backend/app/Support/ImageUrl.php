<?php

namespace App\Support;

/**
 * Turns whatever is in an image column into something a browser can load.
 *
 * Columns hold a path on the public disk. Rows written before images moved
 * out of the database still hold a base64 data URI, and those pass straight
 * through, so old and new data render side by side.
 */
class ImageUrl
{
    public static function for(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (str_starts_with($value, 'data:')
            || str_starts_with($value, 'http://')
            || str_starts_with($value, 'https://')) {
            return $value;
        }

        return asset('storage/' . ltrim($value, '/'));
    }
}
