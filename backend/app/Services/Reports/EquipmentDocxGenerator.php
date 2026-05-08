<?php

namespace App\Services\Reports;

use Carbon\CarbonInterface;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class EquipmentDocxGenerator
{
    public function __construct(
        private array $rows,
        private string $statusFilter,
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

        $section->addText('INVENTORY OF MACHINERY AND EQUIPMENT', ['bold' => true, 'size' => 14], $centered);
        if ($this->statusFilter !== '') {
            $section->addText('Filtered by status: ' . $this->statusFilter, ['italic' => true, 'size' => 10], $centered);
        }
        $section->addText('As of ' . $this->asOfDate->format('F j, Y'), ['italic' => true, 'size' => 10], $centered);
        $section->addTextBreak(1);

        $word->addTableStyle('EquipmentTable', [
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 80,
            'alignment' => Jc::CENTER,
        ]);
        $table = $section->addTable('EquipmentTable');

        $headerCellStyle = ['bgColor' => '0E2E45'];
        $headerFontStyle = ['bold' => true, 'color' => 'FFFFFF', 'size' => 9];

        $headers = ['Machinery and Equipment', 'Brand', 'Property No.', 'Date Acquired', 'Cost', 'Status'];

        $table->addRow();
        foreach ($headers as $h) {
            $table->addCell(null, $headerCellStyle)->addText($h, $headerFontStyle, $centered);
        }

        $cellFont = ['size' => 9];

        foreach ($this->rows as $row) {
            $table->addRow();
            $table->addCell()->addText($row['name'], ['size' => 9, 'bold' => true]);
            $table->addCell()->addText((string) ($row['brand'] ?? ''), $cellFont);
            $table->addCell()->addText((string) ($row['property_no'] ?? ''), $cellFont);
            $table->addCell()->addText(
                $row['date_acquired'] ? $row['date_acquired']->format('F j, Y') : '',
                $cellFont
            );
            $table->addCell()->addText('₱' . number_format($row['cost'], 2), $cellFont, ['alignment' => Jc::END]);
            $table->addCell()->addText((string) $row['status'], $cellFont);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'equipment_report_') . '.docx';
        IOFactory::createWriter($word, 'Word2007')->save($tempPath);

        return $tempPath;
    }
}
