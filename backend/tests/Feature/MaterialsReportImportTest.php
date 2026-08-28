<?php

namespace Tests\Feature;

use App\Models\RawMaterial;
use App\Models\RawMaterialMovement;
use App\Models\Supplier;
use App\Services\Reports\Import\MaterialsDocxParser;
use App\Services\Reports\Import\MaterialsImportApplier;
use App\Services\Reports\Import\MaterialsImportPlanner;
use App\Services\Reports\MaterialsDocxGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The import is tested against a report this app generated, because that is the
 * shape the client's own reports have — the exports were built from them.
 */
class MaterialsReportImportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function reportFor(array $sections): string
    {
        return (new MaterialsDocxGenerator($sections, 'all', now()))->save();
    }

    private function material(array $attributes = []): RawMaterial
    {
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'contact_person' => 'Someone',
            'email' => 'supplier@example.test',
            'phone' => '09000000000',
            'address' => 'Nabua',
        ]);

        return RawMaterial::create(array_merge([
            'name' => 'Lanyard Metal Clip',
            'supplier_id' => $supplier->supplier_id,
            'cost_per_unit' => 6.50,
            'stock_quantity' => 1000,
            'low_stock_threshold' => 200,
            'unit' => 'pcs',
            'department' => 'Digital Customization Center',
        ], $attributes));
    }

    public function test_it_reads_rows_columns_and_departments_out_of_a_docx(): void
    {
        $path = $this->reportFor([
            'Digital Customization Center' => [
                ['name' => 'Lanyard Metal Clip', 'unit' => 'pcs', 'on_display' => 0.0,
                 'sponsored' => 0.0, 'damaged' => 12.0, 'consumed' => 1180.0, 'available' => 1008.0],
            ],
            'Woodworks' => [
                ['name' => 'Solid Oak Wood Planks', 'unit' => 'board', 'on_display' => 2.0,
                 'sponsored' => 0.0, 'damaged' => 0.0, 'consumed' => 88.0, 'available' => 45.5],
            ],
        ]);

        $result = (new MaterialsDocxParser())->parse($path);
        unlink($path);

        $this->assertCount(2, $result['rows'], 'the letterhead table must not be read as inventory');

        [$clip, $planks] = $result['rows'];

        $this->assertSame('Lanyard Metal Clip', $clip['name']);
        $this->assertSame('Digital Customization Center', $clip['department']);
        $this->assertSame(12.0, $clip['damaged']);
        // Printed as "1,180" — the separator must not truncate the figure.
        $this->assertSame(1180.0, $clip['consumed']);
        // Printed as an em dash, which means none rather than unknown.
        $this->assertSame(0.0, $clip['on_display']);

        $this->assertSame('Woodworks', $planks['department']);
        $this->assertSame(45.5, $planks['available']);
    }

    /**
     * The client's own January 2024 report, whose cells are the reason these
     * cases exist. Nothing in it is a bare number: a figure arrives as "516 pcs"
     * or "70 yards", and requiring the whole cell to be numeric read every one
     * of them as zero — an import that would have emptied the inventory while
     * reporting success.
     */
    public function test_it_reads_a_quantity_that_carries_its_unit_inside_the_cell(): void
    {
        $parser = new MaterialsDocxParser();
        $read = fn (array $cells) => $this->invokeNumber($parser, $cells);

        $this->assertSame(516.0, $read(['available' => '516 pcs']));
        $this->assertSame(4050.0, $read(['available' => '4,050']));
        $this->assertSame(70.0, $read(['available' => '70 yards']));
        $this->assertSame(2850.0, $read(['available' => '2850 pcs']));
        $this->assertSame(0.5, $read(['available' => '0.5 gal']));

        // Nothing, in each of the shapes the report writes it.
        $this->assertSame(0.0, $read(['available' => '']));
        $this->assertSame(0.0, $read(['available' => '-']));
        $this->assertSame(0.0, $read(['available' => '—']));
        // A dash still names its unit when an item is out of everything.
        $this->assertSame(0.0, $read(['available' => '- gal']));
    }

    /**
     * The Book Production and Woodworks sections are six columns wide, not
     * seven — they have no Unit column at all, and say "55 pcs" instead.
     */
    public function test_it_takes_the_unit_from_the_quantity_when_there_is_no_unit_column(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'sixcol') . '.docx';
        $this->buildSixColumnReport($path);

        $rows = (new MaterialsDocxParser())->parse($path)['rows'];
        unlink($path);

        $this->assertSame('Woodworks', $rows[0]['department']);
        $this->assertSame('pcs', $rows[0]['unit']);
        $this->assertSame(45.0, $rows[0]['consumed']);
        $this->assertSame(55.0, $rows[0]['available']);

        $this->assertSame('gal', $rows[1]['unit'], 'a dash still names the unit');
        $this->assertSame(0.0, $rows[1]['available']);
    }

    /**
     * The real file carries its Machinery and Equipment inventory in the same
     * document. Those are a different report with different columns, and must
     * not be read in as materials belonging to the last department heading.
     */
    public function test_it_ignores_a_table_that_is_not_an_inventory_of_materials(): void
    {
        $word = new \PhpOffice\PhpWord\PhpWord();
        $section = $word->addSection();

        $section->addText('PEDS Woodworks');
        $materials = $section->addTable();
        foreach ([
            ['Item', 'No. of Units on Display', 'No. of Sponsored Units', 'No. of Damaged Units', 'No. of Units Consumed', 'Available Units for Production'],
            ['CONCEALED HINGES', '', '', '', '12 pcs', '87 pcs'],
        ] as $cells) {
            $materials->addRow();
            foreach ($cells as $cell) {
                $materials->addCell(1500)->addText($cell);
            }
        }

        $section->addText('INVENTORY OF MACHINERY AND EQUIPMENT');
        $equipment = $section->addTable();
        foreach ([
            ['Machinery and Equipment', 'Brand', 'Property No.', 'Date Acquired', 'Cost', 'Status'],
            ['Mug Press', 'CUYI', 'ICS-06-01-21', 'June 1, 2021', 'P3,750.00', 'Non-Serviceable'],
        ] as $cells) {
            $equipment->addRow();
            foreach ($cells as $cell) {
                $equipment->addCell(1500)->addText($cell);
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'mixed') . '.docx';
        \PhpOffice\PhpWord\IOFactory::createWriter($word, 'Word2007')->save($path);

        $rows = (new MaterialsDocxParser())->parse($path)['rows'];
        unlink($path);

        $this->assertCount(1, $rows);
        $this->assertSame('CONCEALED HINGES', $rows[0]['name']);
    }

    public function test_a_cell_it_cannot_read_is_reported_rather_than_silently_zeroed(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'odd') . '.docx';
        $this->buildSixColumnReport($path, third: ['MYSTERY ITEM', '', '', '', 'see attached', 'ask Ma\'am']);

        $result = (new MaterialsDocxParser())->parse($path);
        unlink($path);

        $this->assertSame(0.0, $result['rows'][2]['available']);
        $this->assertNotEmpty($result['warnings']);
        $this->assertStringContainsString('MYSTERY ITEM', implode(' ', $result['warnings']));
    }

    /**
     * Builds the six-column shape used by Book Production and Woodworks.
     */
    private function buildSixColumnReport(string $path, ?array $third = null): void
    {
        $word = new \PhpOffice\PhpWord\PhpWord();
        $section = $word->addSection();
        $section->addText('PEDS Woodworks');

        $rows = [
            ['Item', 'No. of Units on Display', 'No. of Sponsored Units', 'No. of Damaged Units', 'No. of Units Consumed', 'Available Units for Production'],
            ['3/4 MARINE PLYWOOD', '', '', '', '45 pcs', '55 pcs'],
            ['BOYSEN SANDING SEALER', '', '', '', '1 gal', '- gal'],
        ];

        if ($third !== null) {
            $rows[] = $third;
        }

        $table = $section->addTable();
        foreach ($rows as $cells) {
            $table->addRow();
            foreach ($cells as $cell) {
                $table->addCell(1500)->addText($cell);
            }
        }

        \PhpOffice\PhpWord\IOFactory::createWriter($word, 'Word2007')->save($path);
    }

    /**
     * number() is private and takes the cell list positionally; this drives it
     * through a one-row document so the cases above read as data, not plumbing.
     */
    private function invokeNumber(MaterialsDocxParser $parser, array $values): float
    {
        $path = tempnam(sys_get_temp_dir(), 'cell') . '.docx';
        $this->buildSixColumnReport($path, third: ['PROBE', '', '', '', '', $values['available']]);

        $rows = $parser->parse($path)['rows'];
        unlink($path);

        return $rows[2]['available'];
    }

    public function test_it_rejects_a_file_that_is_not_a_docx(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'fake') . '.docx';
        file_put_contents($path, 'this is not a word document');

        $this->expectExceptionMessage('does not look like a Word .docx file');

        try {
            (new MaterialsDocxParser())->parse($path);
        } finally {
            unlink($path);
        }
    }

    public function test_it_plans_matches_misses_and_no_ops_without_writing(): void
    {
        $this->material(['stock_quantity' => 1000, 'units_damaged' => 12]);

        $plan = (new MaterialsImportPlanner())->plan([
            // Matches, and moves stock 1000 -> 1008.
            ['name' => 'Lanyard Metal Clip', 'unit' => 'pcs', 'on_display' => 0.0, 'sponsored' => 0.0,
             'damaged' => 12.0, 'consumed' => 0.0, 'available' => 1008.0, 'department' => null],
            // Nothing of this name exists.
            ['name' => 'Marine Plywood 3/4', 'unit' => 'sheet', 'on_display' => 0.0, 'sponsored' => 0.0,
             'damaged' => 0.0, 'consumed' => 0.0, 'available' => 20.0, 'department' => null],
        ]);

        $this->assertSame('update', $plan['items'][0]['status']);
        $this->assertSame(['from' => 1000.0, 'to' => 1008.0], $plan['items'][0]['changes']['available']);
        $this->assertArrayNotHasKey('damaged', $plan['items'][0]['changes'], 'a figure that already agrees is not a change');

        $this->assertSame('unmatched', $plan['items'][1]['status']);
        $this->assertSame(1, $plan['summary']['unmatched']);

        // Planning is a read.
        $this->assertSame(1000.0, (float) RawMaterial::first()->stock_quantity);
    }

    public function test_matching_ignores_spacing_and_punctuation_drift(): void
    {
        $this->material(['name' => 'Bond Paper (A4, 80gsm)']);

        $plan = (new MaterialsImportPlanner())->plan([
            ['name' => 'Bond  Paper  A4 80gsm', 'unit' => 'pcs', 'on_display' => 0.0, 'sponsored' => 0.0,
             'damaged' => 0.0, 'consumed' => 0.0, 'available' => 1000.0, 'department' => null],
        ]);

        $this->assertSame('Raw Material', $plan['items'][0]['type']);
    }

    public function test_applying_restates_a_material_through_the_ledger(): void
    {
        $material = $this->material(['stock_quantity' => 1000, 'units_consumed' => 0, 'units_damaged' => 0]);

        $plan = (new MaterialsImportPlanner())->plan([
            ['name' => 'Lanyard Metal Clip', 'unit' => 'pcs', 'on_display' => 0.0, 'sponsored' => 0.0,
             'damaged' => 12.0, 'consumed' => 1180.0, 'available' => 1008.0, 'department' => null],
        ]);

        $result = app(MaterialsImportApplier::class)->apply($plan, ['note' => 'Imported from old.docx']);

        $this->assertSame(1, $result['applied']);
        $this->assertSame([], $result['failed']);

        $material->refresh();
        $this->assertSame(1180.0, (float) $material->units_consumed);
        $this->assertSame(12.0, (float) $material->units_damaged);

        // The report's Available column is already net of what it consumed, so
        // stock lands on the reported figure rather than 1000 - 1180 - 12.
        $this->assertSame(1008.0, (float) $material->stock_quantity);

        // And the change is auditable rather than a silent column write.
        $this->assertGreaterThan(0, RawMaterialMovement::count());
        $this->assertSame(
            'Imported from old.docx',
            RawMaterialMovement::latest('movement_id')->first()->note
        );
    }

    public function test_a_restatement_that_lowers_a_counter_is_written_as_a_reversal(): void
    {
        $material = $this->material(['stock_quantity' => 500, 'units_consumed' => 100]);

        $plan = (new MaterialsImportPlanner())->plan([
            ['name' => 'Lanyard Metal Clip', 'unit' => 'pcs', 'on_display' => 0.0, 'sponsored' => 0.0,
             'damaged' => 0.0, 'consumed' => 40.0, 'available' => 500.0, 'department' => null],
        ]);

        app(MaterialsImportApplier::class)->apply($plan);

        $this->assertSame(40.0, (float) $material->refresh()->units_consumed);

        // Not logged as "consumed 60", which is the opposite of what happened.
        $movement = RawMaterialMovement::latest('movement_id')->first();
        $this->assertSame('reversal', $movement->reason->value);
        $this->assertSame(60.0, (float) $movement->quantity);
    }

    public function test_a_full_round_trip_leaves_the_figures_where_the_report_put_them(): void
    {
        $material = $this->material(['stock_quantity' => 1008, 'units_damaged' => 12, 'units_consumed' => 1180]);

        $path = $this->reportFor([
            'Digital Customization Center' => [
                ['name' => 'Lanyard Metal Clip', 'unit' => 'pcs', 'on_display' => 0.0, 'sponsored' => 0.0,
                 'damaged' => 12.0, 'consumed' => 1180.0, 'available' => 1008.0],
            ],
        ]);

        // Someone edits the shelf after the report was filed.
        $material->update(['stock_quantity' => 900, 'units_consumed' => 1288]);

        $rows = (new MaterialsDocxParser())->parse($path)['rows'];
        unlink($path);

        app(MaterialsImportApplier::class)->apply((new MaterialsImportPlanner())->plan($rows));

        $material->refresh();
        $this->assertSame(1008.0, (float) $material->stock_quantity);
        $this->assertSame(1180.0, (float) $material->units_consumed);
        $this->assertSame(12.0, (float) $material->units_damaged);
    }
}
