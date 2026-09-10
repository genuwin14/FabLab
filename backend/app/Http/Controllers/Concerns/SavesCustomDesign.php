<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CustomDesign;
use App\Services\InkEstimator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Write a design the studio posted, and measure its ink while we have the
 * print in hand.
 *
 * Two screens save designs — the studio's Save button and its Add to Cart —
 * and they were each doing the same updateOrCreate. They now share this, so
 * the print the studio exports is handled once: measured into
 * `ink_coverage`, and kept on disk as `print_image` so the reviewer who
 * corrects the figure is looking at the artwork it came from.
 *
 * The print is optional. My Designs re-posts a saved recipe with no print,
 * and an old studio tab may not send one; in both cases whatever the design
 * already had is left alone rather than wiped. A print that GD cannot open
 * is ignored the same way — the estimate from the recipe is a better answer
 * than a zero from a corrupt file.
 */
trait SavesCustomDesign
{
    /**
     * @param  array<string, mixed>  $recipe
     */
    protected function saveCustomDesign(Request $request, int|string|null $designId, int|string $productId, array $recipe): CustomDesign
    {
        $design = CustomDesign::updateOrCreate(
            [
                'custom_design_id' => $designId,
                'user_id' => auth()->id(),
            ],
            [
                'product_id' => $productId,
                'recipe' => $recipe,
                'snapshot' => $request->input('custom_snapshot'),
            ]
        );

        $print = $request->input('custom_print');
        if (is_string($print) && $print !== '') {
            $this->recordPrint($design, $print, $request->input('custom_print_area'));
        }

        return $design;
    }

    private function recordPrint(CustomDesign $design, string $print, $printableFraction): void
    {
        $fraction = is_numeric($printableFraction) ? (float) $printableFraction : 1.0;

        $coverage = app(InkEstimator::class)->measure($print, $fraction);
        if ($coverage === null) {
            return;
        }

        $path = 'designs/prints/' . $design->custom_design_id . '.png';
        $bytes = base64_decode(substr($print, strpos($print, ',') + 1), true);
        if ($bytes !== false) {
            Storage::disk('public')->put($path, $bytes);
        }

        // A second save can't be told from the first by wasChanged() on the
        // recipe alone, so the coverage is written unconditionally: the print
        // is whatever the studio last rendered.
        $design->forceFill([
            'ink_coverage' => $coverage,
            'print_image' => $bytes !== false ? $path : $design->print_image,
        ])->save();
    }
}
