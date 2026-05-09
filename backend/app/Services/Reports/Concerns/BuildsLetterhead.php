<?php

namespace App\Services\Reports\Concerns;

use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

trait BuildsLetterhead
{
    private function addLetterhead(PhpWord $word, Section $section): void
    {
        $word->addTableStyle('LetterheadTable', [
            'borderSize' => 0,
            'cellMargin' => 0,
        ]);

        $table = $section->addTable('LetterheadTable');
        $table->addRow();

        $logoCell = $table->addCell(1500, ['valign' => 'center']);
        $logoPath = public_path('img/CSPC-LOGO.png');
        if (is_file($logoPath)) {
            $logoCell->addImage($logoPath, [
                'width' => 75,
                'height' => 75,
                'alignment' => Jc::START,
            ]);
        }

        // Spacer column to provide a small gap between the logo and the text.
        $table->addCell(120, ['valign' => 'center']);

        $textCell = $table->addCell(8500, ['valign' => 'center']);
        $leftAlign = ['alignment' => Jc::START, 'spaceAfter' => 0];
        $textCell->addText(
            'Republic of the Philippines',
            ['name' => 'Arial', 'italic' => true, 'size' => 11],
            $leftAlign
        );
        $textCell->addText(
            'CAMARINES SUR POLYTECHNIC COLLEGES',
            ['name' => 'Arial', 'bold' => true, 'size' => 11],
            $leftAlign
        );
        $textCell->addText(
            'Nabua, Camarines Sur',
            ['name' => 'Arial', 'italic' => true, 'size' => 11],
            $leftAlign
        );
        $textCell->addText(
            'PRODUCTION AND ENTREPRENEURIAL DEVELOPMENT SERVICES',
            ['name' => 'Arial', 'bold' => true, 'size' => 11],
            $leftAlign
        );

        $section->addText('', [], [
            'borderBottomSize' => 12,
            'borderBottomColor' => '4F81BD',
            'spaceBefore' => 60,
            'spaceAfter' => 240,
        ]);
    }

    private function addBlueFooter(Section $section): void
    {
        $footer = $section->addFooter();

        $footer->addText('', [], [
            'borderTopSize' => 12,
            'borderTopColor' => '4F81BD',
            'spaceAfter' => 0,
        ]);
    }
}
