<?php

namespace App\Services\Reports;

use App\Services\Reports\Concerns\BuildsLetterhead;
use Carbon\CarbonInterface;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class MaterialsDocxGenerator
{
    use BuildsLetterhead;

    public function __construct(
        private array $sections,
        private string $group,
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

        $this->addLetterhead($word, $section);
        $this->addBlueFooter($section);

        $word->addTableStyle('MaterialsTable', [
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 60,
            'alignment' => Jc::CENTER,
        ]);

        $tightCentered = ['alignment' => Jc::CENTER, 'spaceAfter' => 0];
        $headerSpaceAfter = ['alignment' => Jc::CENTER, 'spaceAfter' => 120];

        $first = true;
        foreach ($this->sections as $deptName => $rows) {
            if (! $first) {
                $section->addPageBreak();
            }
            $first = false;

            $sectionLabel = $deptName === 'Uncategorized' ? $deptName : 'PEDS ' . $deptName;

            $section->addText('INVENTORY OF MATERIALS', ['bold' => true, 'size' => 12], $tightCentered);
            $section->addText(strtoupper($sectionLabel), ['bold' => false, 'size' => 12], $tightCentered);
            $section->addText(
                'As of ' . $this->asOfDate->format('F j, Y'),
                ['size' => 12],
                $headerSpaceAfter
            );

            if (empty($rows)) {
                $section->addText(
                    'No items assigned to this section.',
                    ['italic' => true, 'size' => 10, 'color' => '888888'],
                    $centered
                );
                continue;
            }

            $table = $section->addTable('MaterialsTable');
            $headerFontStyle = ['bold' => true, 'size' => 10];

            $headers = [
                ['label' => 'Item', 'width' => 2200],
                ['label' => 'Unit', 'width' => 800],
                ['label' => 'No. of Units on Display', 'width' => 1600],
                ['label' => 'No. of Sponsored Units', 'width' => 1200],
                ['label' => 'No. of Damaged Units', 'width' => 1100],
                ['label' => 'No. of Units Consumed', 'width' => 1200],
                ['label' => 'Available Units for Production', 'width' => 1900],
            ];

            $table->addRow();
            foreach ($headers as $h) {
                $table->addCell($h['width'])->addText($h['label'], $headerFontStyle, $centered);
            }

            $cellFont = ['size' => 10];

            foreach ($rows as $row) {
                $table->addRow();
                $table->addCell($headers[0]['width'])->addText($row['name'], ['size' => 10, 'bold' => true], $centered);
                $table->addCell($headers[1]['width'])->addText((string) ($row['unit'] ?? ''), $cellFont, $centered);
                $table->addCell($headers[2]['width'])->addText($this->fmt($row['on_display']), $cellFont, $centered);
                $table->addCell($headers[3]['width'])->addText($this->fmt($row['sponsored']), $cellFont, $centered);
                $table->addCell($headers[4]['width'])->addText($this->fmt($row['damaged']), $cellFont, $centered);
                $table->addCell($headers[5]['width'])->addText($this->fmt($row['consumed']), $cellFont, $centered);
                $table->addCell($headers[6]['width'])->addText($this->fmt($row['available']), ['size' => 10, 'bold' => true], $centered);
            }
        }

        $section->addTextBreak(1);
        $section->addText(
            'Note: A dash (—) or 0 indicates the item is out of stock or no data is available.',
            ['italic' => true, 'size' => 9, 'color' => '666666']
        );

        $tempPath = tempnam(sys_get_temp_dir(), 'materials_report_') . '.docx';
        IOFactory::createWriter($word, 'Word2007')->save($tempPath);

        return $tempPath;
    }

    private function fmt(float $value): string
    {
        if ($value <= 0) {
            return '—';
        }

        return $value == (int) $value
            ? number_format($value)
            : number_format($value, 2);
    }
}
