<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Department;
use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Texture;
use App\Services\Reports\EquipmentDocxGenerator;
use App\Services\Reports\MaterialsDocxGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function materials(Request $request)
    {
        [$sections, $group] = $this->buildMaterialsSections($request);

        return view('admin.reports.materials', [
            'sections' => $sections,
            'group' => $group,
            'asOfDate' => now(),
        ]);
    }

    public function equipment(Request $request)
    {
        [$rows, $status] = $this->buildEquipmentRows($request);

        return view('admin.reports.equipment', [
            'rows' => $rows,
            'status' => $status,
            'asOfDate' => now(),
        ]);
    }

    public function exportMaterialsPdf(Request $request)
    {
        [$sections, $group] = $this->buildMaterialsSections($request);

        $pdf = Pdf::loadView('admin.reports.pdf.materials', [
            'sections' => $sections,
            'group' => $group,
            'asOfDate' => now(),
        ])->setPaper('a4', 'landscape');

        $filename = 'inventory-materials-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportMaterialsDocx(Request $request)
    {
        [$sections, $group] = $this->buildMaterialsSections($request);

        $generator = new MaterialsDocxGenerator($sections, $group, now());
        $tempPath = $generator->save();

        $filename = 'inventory-materials-' . now()->format('Y-m-d') . '.docx';

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function exportEquipmentPdf(Request $request)
    {
        [$rows, $status] = $this->buildEquipmentRows($request);

        $pdf = Pdf::loadView('admin.reports.pdf.equipment', [
            'rows' => $rows,
            'status' => $status,
            'asOfDate' => now(),
        ])->setPaper('a4', 'landscape');

        $filename = 'inventory-equipment-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportEquipmentDocx(Request $request)
    {
        [$rows, $status] = $this->buildEquipmentRows($request);

        $generator = new EquipmentDocxGenerator($rows, $status, now());
        $tempPath = $generator->save();

        $filename = 'inventory-equipment-' . now()->format('Y-m-d') . '.docx';

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Aggregates Products + RawMaterials + Textures into department-grouped sections:
     * [
     *   'Digital Customization Center' => [...rows...],
     *   'Book Production' => [...],
     *   'Woodworks' => [...],
     *   'Uncategorized' => [...],
     * ]
     * Each row: { type, name, unit, on_display, sponsored, damaged, consumed, available }
     */
    private function buildMaterialsSections(Request $request): array
    {
        $group = $request->query('group', 'all');

        $rows = collect();

        if ($group === 'all' || $group === 'products') {
            $rows = $rows->concat(Product::orderBy('name')->get()->map(fn ($p) => [
                'type' => 'Product',
                'name' => $p->name,
                'unit' => $p->unit,
                'on_display' => (float) $p->units_on_display,
                'sponsored' => (float) $p->units_sponsored,
                'damaged' => (float) $p->units_damaged,
                'consumed' => (float) $p->units_consumed,
                'available' => (float) $p->stock,
                'department' => $p->department,
            ]));
        }

        if ($group === 'all' || $group === 'raw_materials') {
            $rows = $rows->concat(RawMaterial::orderBy('name')->get()->map(fn ($m) => [
                'type' => 'Raw Material',
                'name' => $m->name,
                'unit' => $m->unit,
                'on_display' => (float) $m->units_on_display,
                'sponsored' => (float) $m->units_sponsored,
                'damaged' => (float) $m->units_damaged,
                'consumed' => (float) $m->units_consumed,
                'available' => (float) $m->stock_quantity,
                'department' => $m->department,
            ]));
        }

        if ($group === 'all' || $group === 'textures') {
            $rows = $rows->concat(Texture::orderBy('name')->get()->map(fn ($t) => [
                'type' => 'Texture',
                'name' => $t->name,
                'unit' => $t->unit,
                'on_display' => (float) $t->units_on_display,
                'sponsored' => (float) $t->units_sponsored,
                'damaged' => (float) $t->units_damaged,
                'consumed' => (float) $t->units_consumed,
                'available' => (float) $t->stock_quantity,
                'department' => $t->department,
            ]));
        }

        // Build canonically-ordered sections, always including all 3 + Uncategorized
        // even when empty so the report layout is consistent.
        $sections = [];
        foreach (Department::values() as $dept) {
            $sections[$dept] = $rows->where('department', $dept)->values()->all();
        }
        $sections['Uncategorized'] = $rows
            ->filter(fn ($r) => empty($r['department']))
            ->values()
            ->all();

        return [$sections, $group];
    }

    private function buildEquipmentRows(Request $request): array
    {
        $status = trim((string) $request->query('status', ''));

        $query = Equipment::orderBy('name');
        if ($status !== '') {
            $query->where('status', $status);
        }

        $rows = $query->get()->map(fn ($e) => [
            'name' => $e->name,
            'brand' => $e->brand,
            'property_no' => $e->property_no,
            'date_acquired' => $e->date_acquired,
            'cost' => (float) $e->cost,
            'status' => $e->status,
        ])->all();

        return [$rows, $status];
    }
}
