<?php

namespace App\Enums;

/**
 * Why a raw material's stock moved.
 *
 * The first four mirror the columns the materials report prints, which is the
 * point: recording a movement is now the only thing that touches those
 * counters, so the report and the shelf can't drift apart. The last two are
 * bookkeeping — `Correction` reconciles a physical count, `Reversal` undoes an
 * earlier entry.
 */
enum StockMovementReason: string
{
    case Consumed = 'consumed';
    case Damaged = 'damaged';
    case Sponsored = 'sponsored';
    case OnDisplay = 'on_display';
    case Correction = 'correction';
    case Reversal = 'reversal';

    public function label(): string
    {
        return match ($this) {
            self::Consumed => 'Consumed in production',
            self::Damaged => 'Damaged / spoiled',
            self::Sponsored => 'Sponsored / given away',
            self::OnDisplay => 'Moved to display',
            self::Correction => 'Stock correction',
            self::Reversal => 'Reversal',
        };
    }

    /**
     * Short form for table cells and badges.
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::Consumed => 'Consumed',
            self::Damaged => 'Damaged',
            self::Sponsored => 'Sponsored',
            self::OnDisplay => 'On Display',
            self::Correction => 'Correction',
            self::Reversal => 'Reversal',
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::Consumed => 'Used up making something. Leaves the shelf for good.',
            self::Damaged => 'Spoiled, broken or unusable. Leaves the shelf for good.',
            self::Sponsored => 'Donated or handed to a sponsor. Leaves the shelf for good.',
            self::OnDisplay => 'Shown in the showroom. Still owned, so stock is unchanged.',
            self::Correction => 'Reconcile against a physical count. Enter the counted total.',
            self::Reversal => 'Undoes an earlier movement.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Consumed => 'bi-scissors',
            self::Damaged => 'bi-exclamation-triangle',
            self::Sponsored => 'bi-gift',
            self::OnDisplay => 'bi-easel',
            self::Correction => 'bi-sliders',
            self::Reversal => 'bi-arrow-counterclockwise',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Consumed => '#0d6efd',
            self::Damaged => '#dc3545',
            self::Sponsored => '#fd7e14',
            self::OnDisplay => '#6f42c1',
            self::Correction => '#6c757d',
            self::Reversal => '#198754',
        };
    }

    /**
     * The `units_*` counter this movement adds to, if any. Correction and
     * reversal move stock without belonging to a bucket.
     */
    public function bucketColumn(): ?string
    {
        return match ($this) {
            self::Consumed => 'units_consumed',
            self::Damaged => 'units_damaged',
            self::Sponsored => 'units_sponsored',
            self::OnDisplay => 'units_on_display',
            self::Correction, self::Reversal => null,
        };
    }

    /**
     * Whether the quantity comes off available stock. Display units are still
     * owned by the shop — they're just standing in a cabinet — so they only
     * get tagged.
     */
    public function reducesStock(): bool
    {
        return match ($this) {
            self::Consumed, self::Damaged, self::Sponsored => true,
            self::OnDisplay, self::Correction, self::Reversal => false,
        };
    }

    /**
     * Reasons a person can pick in the Record Usage form. Correction is
     * admin-only, and reversal is never chosen — it comes from the Reverse
     * button on an existing entry.
     *
     * @return array<int, self>
     */
    public static function selectable(bool $includeCorrection = false): array
    {
        $cases = [self::Consumed, self::Damaged, self::Sponsored, self::OnDisplay];

        if ($includeCorrection) {
            $cases[] = self::Correction;
        }

        return $cases;
    }
}
