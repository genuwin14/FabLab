<?php

namespace App\Services\Reports;

use App\Services\Reports\Concerns\BuildsLetterhead;
use Carbon\CarbonInterface;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * The sales report as an editable document, mirroring the PDF section for
 * section so the two never tell different stories.
 */
class SalesDocxGenerator
{
    use BuildsLetterhead;

    public function __construct(
        private array $report,
        private string $rangeLabel,
        private CarbonInterface $asOfDate,
    ) {}

    public function save(): string
    {
        $word = new PhpWord();
        $word->setDefaultFontName('Arial');
        $word->setDefaultFontSize(11);

        $section = $word->addSection([
            'orientation' => 'portrait',
            'marginTop' => Converter::cmToTwip(0.8),
            'marginBottom' => Converter::cmToTwip(1.1),
            'marginLeft' => Converter::cmToTwip(1.5),
            'marginRight' => Converter::cmToTwip(1.5),
        ]);

        $centered = ['alignment' => Jc::CENTER];
        $centeredTight = ['alignment' => Jc::CENTER, 'spaceAfter' => 0];

        $this->addLetterhead($word, $section);
        $this->addBlueFooter($section);

        $section->addText('SALES REPORT', ['bold' => true, 'size' => 12], $centeredTight);
        $section->addText($this->rangeLabel, ['size' => 12], $centeredTight);
        $section->addText(
            $this->report['rangeStart']->format('F j, Y') . ' to ' . $this->report['rangeEnd']->format('F j, Y'),
            ['size' => 12],
            $centeredTight
        );
        $section->addText(
            'Generated ' . $this->asOfDate->format('F j, Y'),
            ['size' => 12],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 120]
        );

        $word->addTableStyle('SalesTable', [
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 60,
            'alignment' => Jc::CENTER,
        ]);

        $headerFont = ['bold' => true, 'size' => 10];
        $cellFont = ['size' => 10];
        $sectionHeading = ['bold' => true, 'size' => 11];
        $headingParagraph = ['spaceBefore' => 200, 'spaceAfter' => 80];

        // ---------------- Summary ----------------
        $section->addText('SUMMARY', $sectionHeading, $headingParagraph);
        $summary = $section->addTable('SalesTable');

        $rows = [
            ['Total revenue (completed orders)', '₱' . number_format($this->report['totalRevenue'], 2)],
            ['Completed orders', number_format($this->report['orderCount'])],
            ['Average order value', '₱' . number_format($this->report['avgOrderValue'], 2)],
            ['Items sold', number_format($this->report['itemsSold'])],
            ['All-time revenue', '₱' . number_format($this->report['allTimeRevenue'], 2)],
        ];

        foreach ($rows as [$label, $value]) {
            $summary->addRow();
            $summary->addCell(Converter::cmToTwip(11))->addText($label, $cellFont);
            $summary->addCell(Converter::cmToTwip(6))->addText($value, ['size' => 10, 'bold' => true], ['alignment' => Jc::END]);
        }

        // ---------------- Best sellers ----------------
        $section->addText('BEST SELLERS', $sectionHeading, $headingParagraph);
        $products = $section->addTable('SalesTable');

        $products->addRow();
        foreach (['Product', 'SKU', 'Qty sold', 'Revenue'] as $heading) {
            $products->addCell()->addText($heading, $headerFont, $centered);
        }

        if (count($this->report['topProducts']) === 0) {
            $products->addRow();
            $products->addCell()->addText('No sales in this period.', $cellFont, $centered);
            $products->addCell()->addText('', $cellFont);
            $products->addCell()->addText('', $cellFont);
            $products->addCell()->addText('', $cellFont);
        }

        foreach ($this->report['topProducts'] as $product) {
            $products->addRow();
            $products->addCell()->addText((string) $product->name, ['size' => 10, 'bold' => true], $centered);
            $products->addCell()->addText((string) ($product->sku ?: ''), $cellFont, $centered);
            $products->addCell()->addText(number_format($product->qty), $cellFont, $centered);
            $products->addCell()->addText('₱' . number_format($product->revenue, 2), $cellFont, $centered);
        }

        // ---------------- Period breakdown ----------------
        $section->addText(
            $this->report['groupByMonth'] ? 'REVENUE BY MONTH' : 'REVENUE BY DAY',
            $sectionHeading,
            $headingParagraph
        );
        $periods = $section->addTable('SalesTable');

        $periods->addRow();
        foreach ([$this->report['groupByMonth'] ? 'Month' : 'Date', 'Orders', 'Revenue'] as $heading) {
            $periods->addCell()->addText($heading, $headerFont, $centered);
        }

        foreach ($this->report['chartLabels'] as $i => $label) {
            $periods->addRow();
            $periods->addCell()->addText((string) $label, $cellFont, $centered);
            $periods->addCell()->addText(number_format($this->report['orderSeries'][$i] ?? 0), $cellFont, $centered);
            $periods->addCell()->addText('₱' . number_format($this->report['revenueSeries'][$i] ?? 0, 2), $cellFont, $centered);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'sales_report_') . '.docx';
        IOFactory::createWriter($word, 'Word2007')->save($tempPath);

        return $tempPath;
    }
}
