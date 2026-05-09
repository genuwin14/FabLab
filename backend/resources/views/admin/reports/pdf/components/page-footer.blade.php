{{-- Footer rendered on every page via DomPDF's page_script callback so that
     both the divider line and the page number appear on every page (not just
     the last). The line is drawn per-page; the text is drawn per-page too. --}}
<script type="text/php">
    if (isset($pdf)) {
        $pdf->page_script(function ($PAGE_NUM, $PAGE_COUNT, $pdf, $fontMetrics) {
            // ── Tweakable spacing (all values in points; 1cm = ~28.35pt) ──
            $sideMargin    = 42.52; // 1.5cm — divider line left/right margin
            $lineBottom    = 26;    // distance from page bottom to the divider
            $pageNumRight  = 40; // distance from page RIGHT edge to the page-number text
            $pageNumBottom = 18;    // distance from page BOTTOM edge to the page-number baseline
            // ──────────────────────────────────────────────────────────────

            $left  = $sideMargin;
            $right = $pdf->get_width() - $sideMargin;
            $lineY = $pdf->get_height() - $lineBottom;

            // Blue divider — same width as the header divider (#4F81BD)
            $pdf->line($left, $lineY, $right, $lineY, [0x4F / 0xFF, 0x81 / 0xFF, 0xBD / 0xFF], 1.5);

            // Page number — right-aligned, independent of the divider margin
            $text  = 'Page ' . $PAGE_NUM . ' of ' . $PAGE_COUNT;
            $font  = $fontMetrics->getFont('Helvetica', 'normal');
            $size  = 9;
            $width = $fontMetrics->getTextWidth($text, $font, $size);
            $textX = $pdf->get_width() - $pageNumRight - $width;
            $textY = $pdf->get_height() - $pageNumBottom;
            $pdf->text($textX, $textY, $text, $font, $size, [0.33, 0.33, 0.33]);
        });
    }
</script>
