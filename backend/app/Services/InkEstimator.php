<?php

namespace App\Services;

use App\Models\CustomDesign;
use App\Models\InkChannel;
use App\Models\Product;
use App\Models\TransferSheet;

/**
 * How much of each ink a design's print takes.
 *
 * Two ways of answering, in order of preference:
 *
 *   - **Measured.** The studio exports the flat print — every panel's
 *     artwork on a transparent canvas, nothing of the garment — and
 *     measure() walks its pixels. Each one is split into the four channels
 *     the printer would lay down (the plain RGB→CMYK conversion, weighted by
 *     the pixel's opacity) and the sums are divided by the printable pixels,
 *     giving the fraction of the print each channel covers. White is no ink,
 *     which is right for sublimation; a transparent pixel is nothing at all.
 *   - **Estimated.** A design saved before prints were exported has only its
 *     recipe. estimate() rebuilds a rough coverage from that: each element's
 *     footprint on the studio's 1024-pixel canvas times its colour's split,
 *     with an uploaded image analysed the same way as a print when its data
 *     is in the recipe. Coarser — it can't see the glyphs in a font — but it
 *     still knows red text takes no cyan and a bigger shape takes more.
 *
 * Either way the result is a coverage fraction per channel, and
 * millilitres() turns it into a draw: coverage × the product's printable
 * area at the ordered size × the channel's rate, keyed by the bottle the
 * channel empties. That is the figure the order screens show and the shop
 * reserves; the reviewer can still correct it against the artwork.
 */
class InkEstimator
{
    /** The channels in the order every result lists them. */
    public const CHANNELS = ['cyan', 'magenta', 'yellow', 'black'];

    /** The side the print is resampled to before counting. See measure(). */
    private const SAMPLE_SIZE = 256;

    /** The studio's design canvas is this many pixels square. */
    private const CANVAS = 1024;

    /**
     * The coverage a pixel of an image is assumed to carry when its data
     * cannot be read: a mid-density full-colour print, the same weighting the
     * old fixed split used.
     */
    private const OPAQUE_IMAGE_COVERAGE = ['cyan' => 0.30, 'magenta' => 0.30, 'yellow' => 0.25, 'black' => 0.15];

    /**
     * Measure a flat print exported by the studio.
     *
     * `$printableFraction` is the share of the canvas the model's printable
     * panels occupy. Coverage is a fraction of the *panels*, not the canvas,
     * because the product's print area describes the panels: a t-shirt's
     * atlas is mostly seams and gaps that never see ink. Clamped so a bad
     * figure can't yield more than 100% coverage.
     *
     * Returns null when the data URL is not a PNG GD can open — the caller
     * then falls back to estimating from the recipe rather than storing
     * garbage.
     *
     * @return array<string, float>|null  channel => 0..1
     */
    public function measure(string $dataUrl, float $printableFraction = 1.0): ?array
    {
        $image = $this->imageFromDataUrl($dataUrl);
        if (! $image) {
            return null;
        }

        try {
            // Resample to a fixed size with alpha preserved. A 1024² export
            // is a million pixels; a quarter of that per side is plenty to
            // measure coverage and keeps this well under a tenth of a second.
            $sample = imagecreatetruecolor(self::SAMPLE_SIZE, self::SAMPLE_SIZE);
            imagealphablending($sample, false);
            imagesavealpha($sample, true);
            imagefill($sample, 0, 0, imagecolorallocatealpha($sample, 0, 0, 0, 127));
            imagecopyresampled($sample, $image, 0, 0, 0, 0, self::SAMPLE_SIZE, self::SAMPLE_SIZE, imagesx($image), imagesy($image));

            $sums = array_fill_keys(self::CHANNELS, 0.0);

            for ($y = 0; $y < self::SAMPLE_SIZE; $y++) {
                for ($x = 0; $x < self::SAMPLE_SIZE; $x++) {
                    $rgba = imagecolorat($sample, $x, $y);
                    // GD alpha runs 0 (opaque) to 127 (transparent).
                    $alpha = ($rgba >> 24) & 0x7F;
                    if ($alpha === 127) {
                        continue;
                    }

                    $opacity = 1 - $alpha / 127;
                    foreach ($this->cmyk(($rgba >> 16) & 0xFF, ($rgba >> 8) & 0xFF, $rgba & 0xFF) as $channel => $value) {
                        $sums[$channel] += $value * $opacity;
                    }
                }
            }

            imagedestroy($sample);
        } finally {
            imagedestroy($image);
        }

        $printable = self::SAMPLE_SIZE * self::SAMPLE_SIZE * max(0.01, min(1.0, $printableFraction));

        return $this->fractions($sums, $printable);
    }

    /**
     * Estimate coverage from a recipe alone.
     *
     * Everything is placed on one full-canvas panel, because the recipe does
     * not record how the model divides its atlas — that lives in the
     * studio's JavaScript. For a model that prints across the whole tile
     * this is exact; for a garment it overstates the panel and so
     * understates coverage a little. Good enough for a fallback that only
     * applies to designs saved before prints were exported.
     *
     * @param  array<string, mixed>  $recipe
     * @return array<string, float>  channel => 0..1
     */
    public function estimate(array $recipe): array
    {
        $elements = $recipe['elements'] ?? [];
        $sums = array_fill_keys(self::CHANNELS, 0.0);

        $add = function (array $coverage, float $pixels) use (&$sums) {
            foreach (self::CHANNELS as $channel) {
                $sums[$channel] += ($coverage[$channel] ?? 0) * $pixels;
            }
        };

        // A shape is a flat fill of one colour: a circle of radius 50 or a
        // 200×20 bar at 1× — the studio's own dimensions.
        foreach ($elements['shapes'] ?? [] as $shape) {
            $scale = $this->scale($shape['scale'] ?? 1, 0.1, 5.0);
            $pixels = ($shape['type'] ?? 'circle') === 'line'
                ? (200 * $scale) * (20 * $scale)
                : M_PI * (50 * $scale) ** 2;

            $add($this->cmykFromHex($shape['color'] ?? null), $pixels);
        }

        // Text is set bold at 48px × scale. Each glyph box is roughly 0.6em
        // wide, and a bold face inks about a third of its box.
        foreach ($elements['text'] ?? [] as $text) {
            $string = trim((string) ($text['text'] ?? ''));
            if ($string === '') {
                continue;
            }

            $scale = $this->scale($text['scale'] ?? 1, 0.5, 4.0);
            $em = 48 * $scale;
            $pixels = mb_strlen($string) * (0.6 * $em) * $em * 0.35;

            $add($this->cmykFromHex($text['color'] ?? null), $pixels);
        }

        // An uploaded image is drawn 200px wide × scale, its height following
        // the aspect. When its pixels are in the recipe they are measured
        // exactly as a print would be; otherwise a mid-density full-colour
        // image is assumed.
        foreach ($elements['logos'] ?? [] as $logo) {
            $scale = $this->scale($logo['scale'] ?? 1, CustomDesign::LOGO_MIN_SCALE, CustomDesign::LOGO_MAX_SCALE);
            $width = 200 * $scale;

            $analysis = is_string($logo['src'] ?? null) ? $this->analyseImage($logo['src']) : null;
            $aspect = $analysis['aspect'] ?? 1.0;
            $coverage = $analysis['coverage'] ?? self::OPAQUE_IMAGE_COVERAGE;

            $add($coverage, $width * ($width / $aspect));
        }

        return $this->fractions($sums, self::CANVAS * self::CANVAS);
    }

    /**
     * The coverage this design prints with: what was measured when it was
     * saved, or an estimate from its recipe when nothing was.
     *
     * @return array{coverage: array<string, float>, source: string}
     */
    public function coverageFor(CustomDesign $design): array
    {
        $stored = $design->ink_coverage;

        if (is_array($stored) && $this->isCoverage($stored)) {
            return ['coverage' => $this->normalise($stored), 'source' => 'measured'];
        }

        return ['coverage' => $this->estimate($design->recipe ?? []), 'source' => 'estimated'];
    }

    /**
     * Whether a measured draw can be worked out for this design at all.
     *
     * Two things have to be true: the product's printable area is known,
     * and at least one channel points at a bottle. Without either there is
     * nothing to multiply by, and the order falls back to the per-element
     * bills of materials as it always did.
     */
    public function applies(CustomDesign $design, Product $product): bool
    {
        return $product->printAreaFor($design->recipe['size'] ?? null) !== null
            && InkChannel::configured();
    }

    /**
     * What one item of this design draws from each ink bottle.
     *
     * Only channels linked to a bottle appear, keyed on that bottle so the
     * caller can accumulate it like any other material. Each entry carries
     * its working — coverage, area, rate — so the order screen can show why
     * the number is what it is.
     *
     * @return array<int, array{quantity: float, channel: string, coverage: float, area: float, rate: float, source: string}>
     */
    public function millilitres(CustomDesign $design, Product $product): array
    {
        $size = $design->recipe['size'] ?? null;
        $area = $product->printAreaFor($size);
        if ($area === null) {
            return [];
        }

        $measured = $this->coverageFor($design);
        $rates = InkChannel::rates();
        $draw = [];

        foreach (InkChannel::linkedMaterials() as $channel => $materialId) {
            $coverage = $measured['coverage'][$channel] ?? 0.0;
            $rate = $rates[$channel]['ml_per_cm2'];

            $draw[$materialId] = [
                'quantity' => round($coverage * $area * $rate, 4),
                'channel' => $channel,
                'coverage' => $coverage,
                'area' => $area,
                'rate' => $rate,
                'source' => $measured['source'],
            ];
        }

        return $draw;
    }

    /**
     * Where the artwork sits on each panel of a print.
     *
     * For every panel, the smallest box around its non-transparent pixels,
     * in canvas UV (0..1 of the whole atlas, the same space the panel is
     * given in). Null for a panel with nothing on it. Keyed like `$zones`.
     *
     * This is what the paper draw and the print size on the order screens
     * come from: the piece the shop cuts from the transfer sheet is this box
     * plus a margin. Sampled at half the studio's resolution, which places
     * an edge within a couple of pixels of the atlas — under a millimetre
     * on a chest.
     *
     * @param  array<int, array<string, mixed>>  $zones
     * @return array<int, array{u0: float, v0: float, u1: float, v1: float}|null>
     */
    public function boundingBoxes(string $dataUrl, array $zones): array
    {
        $boxes = array_fill(0, count($zones), null);
        if ($zones === []) {
            return $boxes;
        }

        $image = $this->imageFromDataUrl($dataUrl);
        if (! $image) {
            return $boxes;
        }

        try {
            $side = 512;
            $sample = imagecreatetruecolor($side, $side);
            imagealphablending($sample, false);
            imagesavealpha($sample, true);
            imagefill($sample, 0, 0, imagecolorallocatealpha($sample, 0, 0, 0, 127));
            imagecopyresampled($sample, $image, 0, 0, 0, 0, $side, $side, imagesx($image), imagesy($image));

            foreach (array_values($zones) as $index => $zone) {
                $x0 = max(0, (int) floor($zone['u0'] * $side));
                $y0 = max(0, (int) floor($zone['v0'] * $side));
                $x1 = min($side - 1, (int) ceil($zone['u1'] * $side) - 1);
                $y1 = min($side - 1, (int) ceil($zone['v1'] * $side) - 1);

                $minX = $minY = PHP_INT_MAX;
                $maxX = $maxY = -1;

                for ($y = $y0; $y <= $y1; $y++) {
                    for ($x = $x0; $x <= $x1; $x++) {
                        // Anything more than faintly there counts: a soft
                        // edge is still cut around.
                        if ((((imagecolorat($sample, $x, $y) >> 24) & 0x7F)) >= 120) {
                            continue;
                        }
                        if ($x < $minX) $minX = $x;
                        if ($x > $maxX) $maxX = $x;
                        if ($y < $minY) $minY = $y;
                        if ($y > $maxY) $maxY = $y;
                    }
                }

                if ($maxX < 0) {
                    continue;
                }

                $boxes[$index] = [
                    'u0' => round($minX / $side, 4),
                    'v0' => round($minY / $side, 4),
                    'u1' => round(($maxX + 1) / $side, 4),
                    'v1' => round(($maxY + 1) / $side, 4),
                ];
            }

            imagedestroy($sample);
        } finally {
            imagedestroy($image);
        }

        return $boxes;
    }

    /**
     * The transfer paper one item of this design takes.
     *
     * The shop prints each panel's artwork on a sheet, cuts the piece out
     * with a margin, and presses it on. So per panel: the artwork's box in
     * centimetres, the piece around it, and what fraction of the sheet
     * that piece is. The fractions add up to the draw, in sheets.
     *
     * Centimetres come from the product's printable area: the atlas's
     * printable panels together cover that many cm², so one unit of atlas
     * width is the square root of area over the panels' UV area. That
     * assumes the atlas is drawn to one scale across its panels and in
     * both directions, which is how the models are measured; a mug wrap
     * that is wider than it is tall comes out a little off, but close
     * enough to cut paper by.
     *
     * Null when it can't be measured — no print area, no panels boxed, or
     * no paper linked — and the order then falls back to the bills of
     * materials.
     *
     * @return array{material_id: int, sheets: float, sheet: array<string, mixed>, panels: array<int, array<string, mixed>>}|null
     */
    public function paper(CustomDesign $design, Product $product): ?array
    {
        if (! TransferSheet::configured()) {
            return null;
        }

        $area = $product->printAreaFor($design->recipe['size'] ?? null);
        $zones = is_array($design->print_zones) ? $design->print_zones : [];
        if ($area === null || $zones === []) {
            return null;
        }

        $uvArea = 0.0;
        foreach ($zones as $zone) {
            $uvArea += max(0, ($zone['u1'] ?? 0) - ($zone['u0'] ?? 0)) * max(0, ($zone['v1'] ?? 0) - ($zone['v0'] ?? 0));
        }
        if ($uvArea <= 0) {
            return null;
        }

        $cmPerUnit = sqrt($area / $uvArea);
        $sheet = TransferSheet::current();
        $sheetArea = $sheet['width_cm'] * $sheet['height_cm'];

        $panels = [];
        $sheets = 0.0;

        foreach ($zones as $zone) {
            $box = $zone['bbox'] ?? null;
            if (! is_array($box)) {
                continue;
            }

            $width = round(($box['u1'] - $box['u0']) * $cmPerUnit, 2);
            $height = round(($box['v1'] - $box['v0']) * $cmPerUnit, 2);
            if ($width <= 0 || $height <= 0) {
                continue;
            }

            $pieceWidth = round($width + 2 * $sheet['margin_cm'], 2);
            $pieceHeight = round($height + 2 * $sheet['margin_cm'], 2);
            $fraction = round(($pieceWidth * $pieceHeight) / $sheetArea, 4);

            // Either way round on the sheet.
            $fits = ($pieceWidth <= $sheet['width_cm'] && $pieceHeight <= $sheet['height_cm'])
                || ($pieceWidth <= $sheet['height_cm'] && $pieceHeight <= $sheet['width_cm']);

            $panels[] = [
                'label' => $zone['label'] ?? 'Panel',
                'width_cm' => $width,
                'height_cm' => $height,
                'piece_width_cm' => $pieceWidth,
                'piece_height_cm' => $pieceHeight,
                'fraction' => $fraction,
                'fits' => $fits,
            ];
            $sheets += $fraction;
        }

        if ($panels === []) {
            return null;
        }

        return [
            'material_id' => (int) $sheet['raw_material_id'],
            'sheets' => round($sheets, 4),
            'sheet' => $sheet,
            'panels' => $panels,
        ];
    }

    /**
     * The four channels a colour resolves to, each 0..1 — the plain
     * conversion, which is what a printer driver without a profile does.
     *
     * @return array<string, float>
     */
    private function cmyk(int $r, int $g, int $b): array
    {
        $r /= 255;
        $g /= 255;
        $b /= 255;

        $k = 1 - max($r, $g, $b);
        if ($k >= 1) {
            return ['cyan' => 0.0, 'magenta' => 0.0, 'yellow' => 0.0, 'black' => 1.0];
        }

        return [
            'cyan' => (1 - $r - $k) / (1 - $k),
            'magenta' => (1 - $g - $k) / (1 - $k),
            'yellow' => (1 - $b - $k) / (1 - $k),
            'black' => $k,
        ];
    }

    /**
     * cmyk() for a CSS hex colour. Anything unreadable is treated as the
     * studio's default element colour, white — which takes no ink.
     *
     * @return array<string, float>
     */
    private function cmykFromHex(?string $hex): array
    {
        $hex = ltrim(trim((string) $hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return $this->cmyk(255, 255, 255);
        }

        return $this->cmyk(hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
    }

    /**
     * Average coverage per pixel of an uploaded image, and its aspect.
     *
     * The same walk as measure(), but the answer is per pixel of the image
     * rather than of a print, because the image is then drawn at whatever
     * size the recipe says.
     *
     * @return array{coverage: array<string, float>, aspect: float}|null
     */
    private function analyseImage(string $dataUrl): ?array
    {
        $image = $this->imageFromDataUrl($dataUrl);
        if (! $image) {
            return null;
        }

        try {
            $width = imagesx($image);
            $height = imagesy($image);
            if ($width < 1 || $height < 1) {
                return null;
            }

            $side = 64;
            $sample = imagecreatetruecolor($side, $side);
            imagealphablending($sample, false);
            imagesavealpha($sample, true);
            imagefill($sample, 0, 0, imagecolorallocatealpha($sample, 0, 0, 0, 127));
            imagecopyresampled($sample, $image, 0, 0, 0, 0, $side, $side, $width, $height);

            $sums = array_fill_keys(self::CHANNELS, 0.0);
            for ($y = 0; $y < $side; $y++) {
                for ($x = 0; $x < $side; $x++) {
                    $rgba = imagecolorat($sample, $x, $y);
                    $alpha = ($rgba >> 24) & 0x7F;
                    if ($alpha === 127) {
                        continue;
                    }
                    $opacity = 1 - $alpha / 127;
                    foreach ($this->cmyk(($rgba >> 16) & 0xFF, ($rgba >> 8) & 0xFF, $rgba & 0xFF) as $channel => $value) {
                        $sums[$channel] += $value * $opacity;
                    }
                }
            }
            imagedestroy($sample);

            return [
                'coverage' => $this->fractions($sums, $side * $side),
                'aspect' => $width / $height,
            ];
        } finally {
            imagedestroy($image);
        }
    }

    /**
     * Open a `data:image/...;base64,` URL with GD. Null for anything else,
     * including a URL to a file — the studio inlines what it uploads, so a
     * plain URL here is a recipe from somewhere this code doesn't trust.
     *
     * @return \GdImage|null
     */
    private function imageFromDataUrl(string $dataUrl): ?\GdImage
    {
        if (! preg_match('#^data:image/(png|jpe?g|gif|webp);base64,(.+)$#s', $dataUrl, $m)) {
            return null;
        }

        $bytes = base64_decode($m[2], true);
        if ($bytes === false || $bytes === '') {
            return null;
        }

        $image = @imagecreatefromstring($bytes);

        return $image instanceof \GdImage ? $image : null;
    }

    /**
     * Turn per-channel sums into fractions of the printable pixels, rounded
     * to four places and clamped to 0..1.
     *
     * @param  array<string, float>  $sums
     * @return array<string, float>
     */
    private function fractions(array $sums, float $printable): array
    {
        $out = [];
        foreach (self::CHANNELS as $channel) {
            $out[$channel] = round(max(0.0, min(1.0, ($sums[$channel] ?? 0) / max(1.0, $printable))), 4);
        }

        return $out;
    }

    /** @param array<string, mixed> $value */
    private function isCoverage(array $value): bool
    {
        foreach (self::CHANNELS as $channel) {
            if (! isset($value[$channel]) || ! is_numeric($value[$channel])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $stored
     * @return array<string, float>
     */
    private function normalise(array $stored): array
    {
        $out = [];
        foreach (self::CHANNELS as $channel) {
            $out[$channel] = max(0.0, min(1.0, (float) $stored[$channel]));
        }

        return $out;
    }

    private function scale($value, float $min, float $max): float
    {
        $scale = is_numeric($value) ? (float) $value : 1.0;

        return max($min, min($max, $scale));
    }
}
