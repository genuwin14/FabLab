<?php

namespace App\Enums;

enum Department: string
{
    case DigitalCustomizationCenter = 'Digital Customization Center';
    case BookProduction = 'Book Production';
    case Woodworks = 'Woodworks';

    /**
     * @return array<string> The canonical ordering used in reports.
     */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
