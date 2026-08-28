<?php

namespace App\Services\Reports\Import;

use App\Enums\Department;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\IOFactory;
use RuntimeException;

/**
 * Reads an Inventory of Materials report back out of a .docx.
 *
 * The worry with importing Word rather than a spreadsheet is that a document
 * has no columns to address. That holds for the legacy binary .doc — PhpWord's
 * MsDoc reader hands back text with the grid thrown away — but not for .docx,
 * which is OOXML: every table survives the round trip as real rows and cells.
 * So this reads the grid rather than inferring it from spacing.
 *
 * Two things still have to be worked out rather than read off:
 *
 * 1. Which tables hold data. The letterhead is a table too, so a table only
 *    counts once its first row matches the known column headings.
 * 2. Which department a table belongs to. The report prints that as a heading
 *    above the table ("PEDS WOODWORKS"), not inside it, so the parser walks the
 *    document in order and remembers the last heading it passed.
 *
 * Columns are found by matching heading text, never by position, so a report
 * that ordered them differently still lands in the right fields.
 */
class MaterialsDocxParser
{
    /**
     * Column heading => field. Matched against a squashed, lowercased form of
     * the cell text, so "No. of Units on Display" and "no of units on display"
     * are the same key.
     */
    private const COLUMNS = [
        'item' => 'name',
        'unit' => 'unit',
        'noofunitsondisplay' => 'on_display',
        'noofsponsoredunits' => 'sponsored',
        'noofdamagedunits' => 'damaged',
        'noofunitsconsumed' => 'consumed',
        'availableunitsforproduction' => 'available',
    ];

    /** A data table has to at least name the item and say what is left of it. */
    private const REQUIRED = ['name', 'available'];

    /**
     * @return array{rows: list<array<string, mixed>>, warnings: list<string>}
     */
    public function parse(string $path): array
    {
        if (! is_readable($path)) {
            throw new RuntimeException('That file could not be read.');
        }

        // Named explicitly: left to sniff the format, PhpWord would accept a
        // .doc and hand back a document with no tables in it at all.
        try {
            $document = IOFactory::load($path, 'Word2007');
        } catch (\Throwable) {
            throw new RuntimeException(
                'That does not look like a Word .docx file. A legacy .doc report has to be saved as .docx first.'
            );
        }

        $rows = [];
        $warnings = [];
        $department = null;
        $tablesSeen = 0;

        foreach ($document->getSections() as $section) {
            foreach ($this->flattenElements($section->getElements()) as $element) {
                if ($element instanceof Table) {
                    $tablesSeen++;
                    $rows = array_merge($rows, $this->parseTable($element, $department, $warnings));

                    continue;
                }

                if ($found = $this->departmentFrom($this->textOf($element))) {
                    $department = $found;
                }
            }
        }

        if ($tablesSeen === 0) {
            throw new RuntimeException('No tables were found in that document.');
        }

        if ($rows === []) {
            throw new RuntimeException(
                'No inventory table was found. The importer looks for a table whose first row names the report columns — Item, Unit, No. of Units on Display, and so on.'
            );
        }

        return ['rows' => $rows, 'warnings' => $warnings];
    }

    /**
     * @param  list<string>  $warnings
     * @return list<array<string, mixed>>
     */
    private function parseTable(Table $table, ?string $department, array &$warnings): array
    {
        $tableRows = $table->getRows();

        if ($tableRows === []) {
            return [];
        }

        $map = $this->mapColumns(array_shift($tableRows));

        // Not an inventory table. The letterhead lands here, and so would any
        // summary someone tacked on underneath.
        if ($map === null) {
            return [];
        }

        $rows = [];
        $widthNeeded = max($map);

        foreach ($tableRows as $row) {
            $cells = array_map(fn ($cell) => $this->textOf($cell), $row->getCells());

            // A merged cell collapses the row's width. Reported rather than
            // half-read, so a silently dropped item can't pass for a clean run.
            if (count($cells) <= $widthNeeded) {
                $label = trim($cells[0] ?? '');

                if ($label !== '') {
                    $warnings[] = sprintf('Skipped a row with too few columns to read: "%s".', $label);
                }

                continue;
            }

            $name = trim($cells[$map['name']]);

            if ($name === '') {
                continue;
            }

            $rows[] = [
                'name' => $name,
                'unit' => isset($map['unit']) ? trim($cells[$map['unit']]) : '',
                'on_display' => $this->number($cells, $map, 'on_display'),
                'sponsored' => $this->number($cells, $map, 'sponsored'),
                'damaged' => $this->number($cells, $map, 'damaged'),
                'consumed' => $this->number($cells, $map, 'consumed'),
                'available' => $this->number($cells, $map, 'available'),
                'department' => $department,
            ];
        }

        return $rows;
    }

    /**
     * Locate each known column in the header row.
     *
     * @return array<string, int>|null  field => column index, or null when this
     *                                  is not an inventory table
     */
    private function mapColumns($headerRow): ?array
    {
        $map = [];

        foreach ($headerRow->getCells() as $index => $cell) {
            $key = $this->squash($this->textOf($cell));

            if (isset(self::COLUMNS[$key])) {
                $map[self::COLUMNS[$key]] = $index;
            }
        }

        foreach (self::REQUIRED as $field) {
            if (! isset($map[$field])) {
                return null;
            }
        }

        return $map;
    }

    /**
     * The department heading sits above its table as plain text. The report
     * writes it as "PEDS Woodworks"; the prefix names the unit that owns the
     * shop and is not part of the department.
     */
    private function departmentFrom(string $text): ?string
    {
        $text = trim($text);

        if ($text === '') {
            return null;
        }

        $candidate = $this->squash(preg_replace('/^peds\s+/i', '', $text) ?? '');

        foreach (Department::values() as $department) {
            if ($candidate === $this->squash($department)) {
                return $department;
            }
        }

        return $candidate === 'uncategorized' ? 'Uncategorized' : null;
    }

    /**
     * @param  list<string>  $cells
     * @param  array<string, int>  $map
     */
    private function number(array $cells, array $map, string $field): float
    {
        if (! isset($map[$field])) {
            return 0.0;
        }

        // The report prints an em dash for "none" and a thousands separator on
        // anything over 999, so neither survives as a number on its own.
        $cleaned = str_replace([',', ' ', "\u{00A0}"], '', trim($cells[$map[$field]]));

        return is_numeric($cleaned) ? (float) $cleaned : 0.0;
    }

    /**
     * Depth-first and in document order — a department heading has to be seen
     * before the table it introduces.
     *
     * @return list<object>
     */
    private function flattenElements(array $elements): array
    {
        $flat = [];

        foreach ($elements as $element) {
            $flat[] = $element;

            // A table's own cells are read by parseTable, not walked here.
            if (! $element instanceof Table && method_exists($element, 'getElements')) {
                $flat = array_merge($flat, $this->flattenElements($element->getElements()));
            }
        }

        return $flat;
    }

    /**
     * PhpWord nests text arbitrarily deep — a cell holds a TextRun holds Text —
     * and getText() on a run returns further elements rather than a string.
     */
    private function textOf($element): string
    {
        if (is_string($element)) {
            return $element;
        }

        if (method_exists($element, 'getText')) {
            $text = $element->getText();

            return is_string($text) ? $text : $this->textOf($text);
        }

        if (method_exists($element, 'getElements')) {
            return implode('', array_map(fn ($child) => $this->textOf($child), $element->getElements()));
        }

        return '';
    }

    /** Lowercased and stripped to letters and digits. */
    private function squash(string $text): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', $text) ?? '');
    }
}
