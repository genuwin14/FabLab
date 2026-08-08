<?php

namespace App\Enums;

/**
 * The units a raw material can be counted in.
 *
 * This was a free-text field, so the same thing got typed three ways — "m",
 * "meter", "metre" — and a bill of materials asking for 0.9 of one had no way
 * to know which. The list below is the whole vocabulary; each case carries a
 * typical FabLab material so whoever is adding stock can see at a glance that
 * ink goes in grams and lanyard strap goes in metres.
 */
enum MaterialUnit: string
{
    // Count
    case Pcs = 'pcs';
    case Set = 'set';
    case Pair = 'pair';
    case Box = 'box';
    case Pack = 'pack';
    case Ream = 'ream';
    case Roll = 'roll';
    case Sheet = 'sheet';
    case Bundle = 'bundle';

    // Length
    case Millimeter = 'mm';
    case Centimeter = 'cm';
    case Meter = 'meter';
    case Inch = 'inch';
    case Foot = 'foot';
    case Yard = 'yard';

    // Area
    case SquareMeter = 'sq m';
    case SquareFoot = 'sq ft';

    // Weight
    case Gram = 'gram';
    case Kilogram = 'kilogram';

    // Volume
    case Milliliter = 'ml';
    case Liter = 'liter';
    case Gallon = 'gallon';

    public function group(): string
    {
        return match ($this) {
            self::Pcs, self::Set, self::Pair, self::Box,
            self::Pack, self::Ream, self::Roll, self::Sheet, self::Bundle => 'Count',

            self::Millimeter, self::Centimeter, self::Meter,
            self::Inch, self::Foot, self::Yard => 'Length',

            self::SquareMeter, self::SquareFoot => 'Area',

            self::Gram, self::Kilogram => 'Weight',

            self::Milliliter, self::Liter, self::Gallon => 'Volume',
        };
    }

    /**
     * A material you would actually measure this way. Shown beside the unit in
     * the dropdown so the choice is obvious without a manual.
     */
    public function example(): string
    {
        return match ($this) {
            self::Pcs => 'clips, card holders, transfer paper',
            self::Set => 'tool sets, hardware kits',
            self::Pair => 'hinges, handles',
            self::Box => 'staples, rivets',
            self::Pack => 'blades, sanding discs',
            self::Ream => 'bond paper',
            self::Roll => 'filament, vinyl, tarpaulin',
            self::Sheet => 'acrylic, plywood, vellum board',
            self::Bundle => 'dowels, wood strips',

            self::Millimeter => 'thin trim, fine wire',
            self::Centimeter => 'ribbon, cord',
            self::Meter => 'lanyard strap, fabric, cabling',
            self::Inch => 'imported timber, pipe',
            self::Foot => 'moulding, edging',
            self::Yard => 'bulk fabric',

            self::SquareMeter => 'sheet stock sold by area',
            self::SquareFoot => 'laminate, veneer',

            self::Gram => 'ink, resin powder, pigment',
            self::Kilogram => 'filament, resin, adhesive',

            self::Milliliter => 'ink, solvent, adhesive',
            self::Liter => 'varnish, paint, thinner',
            self::Gallon => 'bulk paint',
        };
    }

    /**
     * Cases keyed by group, in the order the dropdown lists them.
     *
     * @return array<string, array<int, self>>
     */
    public static function grouped(): array
    {
        $groups = [];

        foreach (self::cases() as $case) {
            $groups[$case->group()][] = $case;
        }

        return $groups;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    /**
     * What an existing material is allowed to be saved as.
     *
     * A row created before this list existed may hold something not in it. Its
     * own value stays acceptable, so editing an unrelated field — the cost, the
     * supplier — doesn't force a unit change nobody asked for. New materials
     * get the canonical list only.
     *
     * @return array<int, string>
     */
    public static function allowedFor(?string $currentUnit): array
    {
        $values = self::values();

        if ($currentUnit !== null && $currentUnit !== '' && ! in_array($currentUnit, $values, true)) {
            $values[] = $currentUnit;
        }

        return $values;
    }
}
