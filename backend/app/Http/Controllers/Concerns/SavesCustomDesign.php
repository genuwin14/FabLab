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
 * corrects the figure is looking at the artwork it came from. The panels'
 * rectangles come with it as `print_zones`, so that artwork can be shown
 * panel by panel rather than as the whole atlas.
 *
 * The print is optional. My Designs re-posts a saved recipe with no print,
 * and an old studio tab may not send one; in both cases whatever the design
 * already had is left alone rather than wiped. A print that GD cannot open
 * is ignored the same way — the estimate from the recipe is a better answer
 * than a zero from a corrupt file.
 */
trait SavesCustomDesign
{
    /** The most panels a model is allowed to declare. A shirt has four. */
    private const MAX_PRINT_ZONES = 12;

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
            $this->recordPrint($design, $print, $request->input('custom_print_area'), $request->input('custom_print_zones'));
        }

        return $design;
    }

    private function recordPrint(CustomDesign $design, string $print, $printableFraction, $zones): void
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
            'print_zones' => $this->cleanZones($zones),
        ])->save();
    }

    /**
     * The panel rectangles as the studio sent them, checked.
     *
     * Each is a UV rectangle, 0..1 on both axes, with a label. Anything that
     * isn't — a coordinate off the atlas, a rectangle with no area, a row
     * that isn't an object — is dropped rather than stored, because a bad
     * rectangle crops to nothing and would show a blank panel. Null when
     * nothing usable came, and the order screens then show the whole print.
     *
     * @return array<int, array<string, mixed>>|null
     */
    private function cleanZones($zones): ?array
    {
        if (is_string($zones)) {
            $zones = json_decode($zones, true);
        }
        if (! is_array($zones)) {
            return null;
        }

        $clean = [];
        foreach (array_slice(array_values($zones), 0, self::MAX_PRINT_ZONES) as $zone) {
            if (! is_array($zone)) {
                continue;
            }

            $edges = [];
            foreach (['u0', 'v0', 'u1', 'v1'] as $edge) {
                $value = $zone[$edge] ?? null;
                if (! is_numeric($value) || $value < 0 || $value > 1) {
                    continue 2;
                }
                $edges[$edge] = round((float) $value, 4);
            }
            if ($edges['u1'] <= $edges['u0'] || $edges['v1'] <= $edges['v0']) {
                continue;
            }

            $label = trim((string) ($zone['label'] ?? ''));

            $clean[] = $edges + [
                'id' => mb_substr(trim((string) ($zone['id'] ?? '')), 0, 40),
                'label' => $label === '' ? 'Panel' : mb_substr($label, 0, 40),
                'flipU' => ! empty($zone['flipU']),
                'flipV' => ! empty($zone['flipV']),
            ];
        }

        return $clean === [] ? null : $clean;
    }
}
