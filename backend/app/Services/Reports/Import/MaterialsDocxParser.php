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
                'unit' => $this->unit($cells, $map),
                'on_display' => $this->number($cells, $map, 'on_display', $name, $warnings),
                'sponsored' => $this->number($cells, $map, 'sponsored', $name, $warnings),
                'damaged' => $this->number($cells, $map, 'damaged', $name, $warnings),
                'consumed' => $this->number($cells, $map, 'consumed', $name, $warnings),
                'available' => $this->number($cells, $map, 'available', $name, $warnings),
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
     * The unit an item is measured in.
     *
     * The two shapes seen in real reports: a Unit column of its own, or — in the
     * Book Production and Woodworks sections, which are six columns wide rather
     * than seven — no such column, with the unit written into the quantity cells
     * instead ("55 pcs", "70 yards"). The first quantity that carries one wins.
     *
     * @param  list<string>  $cells
     * @param  array<string, int>  $map
     */
    private function unit(array $cells, array $map): string
    {
        if (isset($map['unit']) && ($declared = trim($cells[$map['unit']])) !== '') {
            return $declared;
        }

        foreach (['available', 'consumed', 'damaged', 'sponsored', 'on_display'] as $field) {
            if (! isset($map[$field])) {
                continue;
            }

            // Either a figure or a dash can carry the unit: an item that is out
            // of everything still says what it is out of ("- gal").
            if (preg_match('/^(?:[\d,]+(?:\.\d+)?|[-–—])\s*([A-Za-z][A-Za-z.\/]*)\.?$/u', trim($cells[$map[$field]]), $match)) {
                return $match[1];
            }
        }

        return '';
    }

    /**
     * Read a quantity out of a cell.
     *
     * Real reports do not hold bare numbers. A figure arrives as "516 pcs" or
     * "70 yards", with the unit inside the cell; over 999 it carries a thousands
     * separator; and "none" is written as a dash, which the report's own footnote
     * says may equally mean "no data available". So the leading number is taken
     * and the rest discarded — requiring the whole cell to be numeric read every
     * "516 pcs" in the file as zero, which would have emptied the inventory
     * rather than filled it.
     *
     * A cell that is neither empty, a dash, nor led by a number is reported
     * instead of quietly becoming zero.
     *
     * @param  list<string>  $cells
     * @param  array<string, int>  $map
     * @param  list<string>  $warnings
     */
    private function number(array $cells, array $map, string $field, string $itemName, array &$warnings): float
    {
        if (! isset($map[$field])) {
            return 0.0;
        }

        $raw = trim($cells[$map[$field]]);

        // Empty, or a dash — any of the ones Word substitutes as you type. The
        // unit may still be spelled out beside it ("- gal"), which is none of
        // something rather than an unreadable cell.
        if ($raw === '' || preg_match('/^[-–—]\s*[A-Za-z.\/]*\.?$/u', $raw)) {
            return 0.0;
        }

        $cleaned = str_replace([',', ' ', "\u{00A0}"], '', $raw);

        if (preg_match('/^\d+(?:\.\d+)?/', $cleaned, $match)) {
            return (float) $match[0];
        }

        $warnings[] = sprintf('Could not read a number from "%s" for %s — treated as 0.', $raw, $itemName);

        return 0.0;
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
