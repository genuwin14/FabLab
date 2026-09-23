<?php

namespace App\Services\Reports;

use App\Services\Reports\Concerns\BuildsLetterhead;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * The order list as an editable document, laid out like the PDF so the two
 * never tell different stories. Landscape, for the nine columns.
 */
class OrdersDocxGenerator
{
    use BuildsLetterhead;

    /** Column headings and widths in cm; the widths fill a landscape A4 inside 1.5cm margins. */
    private const COLUMNS = [
        ['Order No.', 3.2],
        ['Date', 2.2],
        ['Customer', 2.9],
        ['Ordered For', 3.7],
        ['Paid Through', 2.2],
        ['Items', 5.3],
        ['Receipt / PR No.', 2.4],
        ['Status', 2.4],
        ['Total', 2.4],
    ];

    public function __construct(private array $report) {}

    public function save(): string
    {
        // PhpWord writes text into the XML as-is unless told to escape it, and
        // customers type the names and offices printed here: one "&" and Word
        // calls the file corrupt. The setting is global, so it goes back after.
        $escaping = Settings::isOutputEscapingEnabled();
        Settings::setOutputEscapingEnabled(true);

        try {
            return $this->write();
        } finally {
            Settings::setOutputEscapingEnabled($escaping);
        }
    }

    private function write(): string
    {
        $word = new PhpWord();
        $word->setDefaultFontName('Arial');
        $word->setDefaultFontSize(10);

        $section = $word->addSection([
            'orientation' => 'landscape',
            'marginTop' => Converter::cmToTwip(0.8),
            'marginBottom' => Converter::cmToTwip(1.1),
            'marginLeft' => Converter::cmToTwip(1.5),
            'marginRight' => Converter::cmToTwip(1.5),
        ]);

        $centeredTight = ['alignment' => Jc::CENTER, 'spaceAfter' => 0];
        $tight = ['spaceAfter' => 0];

        $this->addLetterhead($word, $section);
        $this->addBlueFooter($section);

        $section->addText('ORDERS', ['bold' => true, 'size' => 12], $centeredTight);
        $section->addText($this->report['filterLabel'], ['size' => 11], $centeredTight);
        $section->addText(
            'Generated ' . $this->report['generatedAt']->format('F j, Y'),
            ['size' => 10],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 160]
        );

        $word->addTableStyle('OrdersTable', [
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 50,
        ]);

        $cellFont = ['size' => 8.5];
        $boldCell = ['size' => 8.5, 'bold' => true];
        $right = ['alignment' => Jc::END, 'spaceAfter' => 0];

        // ---------------- Summary ----------------
        $summary = $section->addTable('OrdersTable');
        $cancelled = $this->report['cancelledCount'];
        $rows = [
            ['Orders listed', number_format($this->report['count'])],
            [
                'Value of the orders not cancelled' . ($cancelled ? ' (' . number_format($cancelled) . ' cancelled left out)' : ''),
                '₱' . number_format($this->report['openValue'], 2),
            ],
        ];

        foreach ($rows as [$label, $value]) {
            $summary->addRow();
            $summary->addCell(Converter::cmToTwip(20))->addText($label, ['size' => 10], $tight);
            $summary->addCell(Converter::cmToTwip(6.7))->addText($value, ['size' => 10, 'bold' => true], $right);
        }

        $section->addText('', [], ['spaceAfter' => 120]);

        // ---------------- Orders ----------------
        $orders = $section->addTable('OrdersTable');

        // Repeated at the top of every page the table runs onto.
        $orders->addRow(null, ['tblHeader' => true]);
        foreach (self::COLUMNS as [$heading, $width]) {
            $orders->addCell(Converter::cmToTwip($width), ['bgColor' => 'F1F4F8', 'valign' => 'center'])
                ->addText($heading, $boldCell, $tight);
        }

        if ($this->report['rows'] === []) {
            $orders->addRow();
            $orders->addCell(Converter::cmToTwip(26.7), ['gridSpan' => count(self::COLUMNS)])
                ->addText('No orders match these filters.', $cellFont, $centeredTight);
        }

        foreach ($this->report['rows'] as $row) {
            // A row split across two pages loses its order number on the second.
            $orders->addRow(null, ['cantSplit' => true]);

            $values = [
                $row['number'],
                $row['placed']->format('M j, Y'),
                $row['customer'],
                $row['ordered_for'],
                $row['channel'],
                null, // the items, one line each, below
                $row['reference'] !== '' ? $row['reference'] : '—',
                $row['status'],
                '₱' . number_format($row['total'], 2),
            ];

            foreach (self::COLUMNS as $i => [, $width]) {
                $cell = $orders->addCell(Converter::cmToTwip($width));

                if ($i === 5) {
                    foreach ($row['items'] ?: ['—'] as $line) {
                        $cell->addText($line, $cellFont, $tight);
                    }

                    continue;
                }

                $cell->addText((string) $values[$i], $cellFont, $i === 8 ? $right : $tight);
            }
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'orders_report_') . '.docx';
        IOFactory::createWriter($word, 'Word2007')->save($tempPath);

        return $tempPath;
    }
}
