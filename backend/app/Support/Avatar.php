<?php

namespace App\Support;

use App\Models\User;

/**
 * A user's picture: their photo when they have one, otherwise their initials
 * on a coloured square (the views crop it to a circle), drawn here as an SVG.
 *
 * The initials used to come from ui-avatars.com, so every page for someone
 * without a photo waited on a third-party site. When that site stopped
 * answering, the browser console filled with ERR_CONNECTION_TIMED_OUT and the
 * navbar showed a broken image. An inline SVG needs no request at all, so it
 * also keeps working on a network that blocks outside sites.
 */
class Avatar
{
    /**
     * @param  string  $background  hex colour, with or without the #
     * @param  string  $color  hex colour of the letters
     * @param  string  $fallbackName  whose initials to draw when the account has no name
     */
    public static function for(?User $user, string $background, string $color, string $fallbackName = 'User'): string
    {
        if ($user && filled($user->photo)) {
            return ImageUrl::for($user->photo);
        }

        return self::initials(filled($user?->fullname) ? $user->fullname : $fallbackName, $background, $color);
    }

    /**
     * "John Doe" → JD, "Madonna" → M, as a data: URI an <img> can take.
     */
    public static function initials(string $name, string $background, string $color): string
    {
        $words = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $letters = mb_substr($words[0] ?? '?', 0, 1);
        if (count($words) > 1) {
            $letters .= mb_substr($words[count($words) - 1], 0, 1);
        }

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
            . '<rect width="64" height="64" fill="#%s"/>'
            . '<text x="50%%" y="50%%" dy=".35em" text-anchor="middle" font-family="Segoe UI, Roboto, Helvetica, Arial, sans-serif" font-size="26" font-weight="600" fill="#%s">%s</text>'
            . '</svg>',
            self::hex($background, '0e2e45'),
            self::hex($color, 'ffffff'),
            htmlspecialchars(mb_strtoupper($letters), ENT_XML1 | ENT_QUOTES, 'UTF-8')
        );

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private static function hex(string $value, string $default): string
    {
        $value = ltrim($value, '#');

        return preg_match('/^(?:[0-9a-fA-F]{3}){1,2}$/', $value) ? $value : $default;
    }
}
