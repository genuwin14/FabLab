<?php

namespace App\Services\Reports;

use Carbon\CarbonInterface;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class MaterialsDocxGenerator
{
    public function __construct(
        private array $sections,
        private string $group,
        private CarbonInterface $asOfDate,
    ) {}

    public function save(): string
    {
        $word = new PhpWord();

        $section = $word->addSection([
            'orientation' => 'landscape',
            'marginTop' => Converter::cmToTwip(1.5),
            'marginBottom' => Converter::cmToTwip(1.5),
            'marginLeft' => Converter::cmToTwip(1.5),
            'marginRight' => Converter::cmToTwip(1.5),
        ]);

        $centered = ['alignment' => Jc::CENTER];

        $section->addText('INVENTORY OF MATERIALS', ['bold' => true, 'size' => 14], $centered);
        $section->addText('As of ' . $this->asOfDate->format('F j, Y'), ['italic' => true, 'size' => 10], $centered);
        $section->addTextBreak(1);

        $word->addTableStyle('MaterialsTable', [
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 80,
            'alignment' => Jc::CENTER,
        ]);

        $first = true;
        foreach ($this->sections as $deptName => $rows) {
            if (! $first) {
                $section->addTextBreak(1);
            }
            $first = false;

            $sectionLabel = $deptName === 'Uncategorized' ? $deptName : 'PEDS ' . $deptName;
            $section->addText(
                strtoupper($sectionLabel),
                ['bold' => true, 'size' => 11],
                $centered
            );
            $section->addTextBreak(1);

            if (empty($rows)) {
                $section->addText(
                    'No items assigned to this section.',
                    ['italic' => true, 'size' => 9, 'color' => '888888'],
                    $centered
                );
                continue;
            }

            $table = $section->addTable('MaterialsTable');
            $headerCellStyle = ['bgColor' => '0E2E45'];
            $headerFontStyle = ['bold' => true, 'color' => 'FFFFFF', 'size' => 9];

            $headers = [
                'Type', 'Item', 'Unit',
                'On Display', 'Sponsored', 'Damaged', 'Consumed',
                'Available for Production',
            ];

            $table->addRow();
            foreach ($headers as $h) {
                $table->addCell(null, $headerCellStyle)->addText($h, $headerFontStyle, $centered);
            }

            $cellFont = ['size' => 9];
            $rightAlign = ['alignment' => Jc::END];

            foreach ($rows as $row) {
                $table->addRow();
                $table->addCell()->addText($row['type'], $cellFont);
                $table->addCell()->addText($row['name'], ['size' => 9, 'bold' => true]);
                $table->addCell()->addText((string) ($row['unit'] ?? ''), $cellFont);
                $table->addCell()->addText($this->fmt($row['on_display']), $cellFont, $rightAlign);
                $table->addCell()->addText($this->fmt($row['sponsored']), $cellFont, $rightAlign);
                $table->addCell()->addText($this->fmt($row['damaged']), $cellFont, $rightAlign);
                $table->addCell()->addText($this->fmt($row['consumed']), $cellFont, $rightAlign);
                $table->addCell()->addText($this->fmt($row['available']), ['size' => 9, 'bold' => true], $rightAlign);
            }
        }

        $section->addTextBreak(1);
        $section->addText(
            'Note: A dash (—) or 0 indicates the item is out of stock or no data is available.',
            ['italic' => true, 'size' => 8, 'color' => '666666']
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
