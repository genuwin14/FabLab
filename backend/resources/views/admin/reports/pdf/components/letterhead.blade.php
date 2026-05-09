{{-- Letterhead drawn on every page via DomPDF's page_script callback.
     This guarantees the logo + 4 institutional lines + blue divider appear at
     the top of every page (the body-flow approach only shows on page 1). --}}
@php
    $cspcLogoPath = public_path('img/CSPC-LOGO.png');
@endphp
<script type="text/php">
    if (isset($pdf)) {
        $logoPath = {!! json_encode($cspcLogoPath) !!};

        $pdf->page_script(function ($PAGE_NUM, $PAGE_COUNT, $pdf, $fontMetrics) use ($logoPath) {
            // A4 portrait: width 595.28pt | side margin 1.5cm = 42.52pt
            $left  = 42.52;
            $right = $pdf->get_width() - 42.52;

            // Logo (CSPC-LOGO.png) at top-left — reduced top margin
            if (is_string($logoPath) && file_exists($logoPath)) {
                $pdf->image($logoPath, $left, 12, 55, 55);
            }

            // Institutional text — left-aligned, vertically aligned with the logo
            // (first line baseline ≈ logo top + ascender height so the top of the
            // first character matches the top of the logo).
            $textX  = $left + 65;            // 55pt logo width + ~10pt gap
            $italic = $fontMetrics->getFont('Helvetica', 'italic');
            $bold   = $fontMetrics->getFont('Helvetica', 'bold');
            $size   = 11;
            $black  = [0, 0, 0];

            $pdf->text($textX, 14, 'Republic of the Philippines',                          $italic, $size, $black);
            $pdf->text($textX, 27, 'CAMARINES SUR POLYTECHNIC COLLEGES',                   $bold,   $size, $black);
            $pdf->text($textX, 40, 'Nabua, Camarines Sur',                                 $italic, $size, $black);
            $pdf->text($textX, 53, 'PRODUCTION AND ENTREPRENEURIAL DEVELOPMENT SERVICES',  $bold,   $size, $black);

            // Blue divider directly below the letterhead block
            $pdf->line($left, 72, $right, 72, [0x4F / 0xFF, 0x81 / 0xFF, 0xBD / 0xFF], 1.5);
        });
    }
</script>
