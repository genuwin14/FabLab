<?php

namespace App\Services\Reports;

use App\Services\Reports\Concerns\BuildsLetterhead;
use Carbon\CarbonInterface;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class EquipmentDocxGenerator
{
    use BuildsLetterhead;

    public function __construct(
        private array $rows,
        private string $statusFilter,
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

        $section->addText('INVENTORY OF MACHINERY AND EQUIPMENT', ['bold' => true, 'size' => 12], $centeredTight);
        if ($this->statusFilter !== '') {
            $section->addText('Filtered by status: ' . $this->statusFilter, ['size' => 12], $centeredTight);
        }
        $section->addText(
            'As of ' . $this->asOfDate->format('F j, Y'),
            ['size' => 12],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 120]
        );

        $word->addTableStyle('EquipmentTable', [
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 60,
            'alignment' => Jc::CENTER,
        ]);
        $table = $section->addTable('EquipmentTable');

        $headerFontStyle = ['bold' => true, 'size' => 10];

        $headers = ['Machinery and Equipment', 'Brand', 'Property No.', 'Date Acquired', 'Cost', 'Status'];

        $table->addRow();
        foreach ($headers as $h) {
            $table->addCell()->addText($h, $headerFontStyle, $centered);
        }

        $cellFont = ['size' => 10];

        foreach ($this->rows as $row) {
            $table->addRow();
            $table->addCell()->addText($row['name'], ['size' => 10, 'bold' => true], $centered);
            $table->addCell()->addText((string) ($row['brand'] ?? ''), $cellFont, $centered);
            $table->addCell()->addText((string) ($row['property_no'] ?? ''), $cellFont, $centered);
            $table->addCell()->addText(
                $row['date_acquired'] ? $row['date_acquired']->format('F j, Y') : '',
                $cellFont,
                $centered
            );
            $table->addCell()->addText('₱' . number_format($row['cost'], 2), $cellFont, $centered);
            $table->addCell()->addText((string) $row['status'], $cellFont, $centered);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'equipment_report_') . '.docx';
        IOFactory::createWriter($word, 'Word2007')->save($tempPath);

        return $tempPath;
    }
}
