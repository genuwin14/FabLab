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
        return redirect()->route('admin.reports.materials');
    }

    public function materials(Request $request)
    {
        [$sections, $group, $dateFrom, $dateTo, $search] = $this->buildMaterialsSections($request);

        return view('admin.reports.materials', [
            'sections' => $sections,
            'group' => $group,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'search' => $search,
            'asOfDate' => now(),
        ]);
    }

    public function equipment(Request $request)
    {
        [$rows, $status, $dateFrom, $dateTo, $search] = $this->buildEquipmentRows($request);

        return view('admin.reports.equipment', [
            'rows' => $rows,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'search' => $search,
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
        ])->setPaper('a4', 'portrait')
          ->setOption('isPhpEnabled', true);

        $deptSlug = $request->query('department') ? '-' . str()->slug($request->query('department')) : '';
        $filename = 'inventory-materials' . $deptSlug . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function previewMaterials(Request $request)
    {
        [$sections, $group] = $this->buildMaterialsSections($request);

        $pdf = Pdf::loadView('admin.reports.pdf.materials', [
            'sections' => $sections,
            'group' => $group,
            'asOfDate' => now(),
        ])->setPaper('a4', 'portrait')
          ->setOption('isPhpEnabled', true);

        $deptSlug = $request->query('department') ? '-' . str()->slug($request->query('department')) : '';
        $filename = 'inventory-materials' . $deptSlug . '-preview.pdf';

        return $pdf->stream($filename);
    }

    public function exportMaterialsDocx(Request $request)
    {
        [$sections, $group] = $this->buildMaterialsSections($request);

        $generator = new MaterialsDocxGenerator($sections, $group, now());
        $tempPath = $generator->save();

        $deptSlug = $request->query('department') ? '-' . str()->slug($request->query('department')) : '';
        $filename = 'inventory-materials' . $deptSlug . '-' . now()->format('Y-m-d') . '.docx';

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function exportEquipmentPdf(Request $request)
    {
        [$rows, $status] = $this->buildEquipmentRows($request);

        $pdf = Pdf::loadView('admin.reports.pdf.equipment', [
            'rows' => $rows,
            'status' => $status,
            'asOfDate' => now(),
        ])->setPaper('a4', 'portrait')
          ->setOption('isPhpEnabled', true);

        $filename = 'inventory-equipment-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function previewEquipment(Request $request)
    {
        [$rows, $status] = $this->buildEquipmentRows($request);

        $pdf = Pdf::loadView('admin.reports.pdf.equipment', [
            'rows' => $rows,
            'status' => $status,
            'asOfDate' => now(),
        ])->setPaper('a4', 'portrait')
          ->setOption('isPhpEnabled', true);

        $filename = 'inventory-equipment-preview.pdf';

        return $pdf->stream($filename);
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
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));
        $search = trim((string) $request->query('search', ''));
        $department = trim((string) $request->query('department', ''));

        $applyFilters = function ($query) use ($dateFrom, $dateTo, $search) {
            if ($dateFrom !== '') {
                $query->whereDate('updated_at', '>=', $dateFrom);
            }
            if ($dateTo !== '') {
                $query->whereDate('updated_at', '<=', $dateTo);
            }
            if ($search !== '') {
                $query->where('name', 'like', '%' . $search . '%');
            }
            return $query;
        };

        $rows = collect();

        if ($group === 'all' || $group === 'products') {
            $query = Product::orderBy('name');
            $applyFilters($query);
            $rows = $rows->concat($query->get()->map(fn ($p) => [
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
            $query = RawMaterial::orderBy('name');
            $applyFilters($query);
            $rows = $rows->concat($query->get()->map(fn ($m) => [
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
            $query = Texture::orderBy('name');
            $applyFilters($query);
            $rows = $rows->concat($query->get()->map(fn ($t) => [
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
        // even when empty so the report layout is consistent. When `department` is
        // set (per-section export), restrict to that one section only.
        $sections = [];
        foreach (Department::values() as $dept) {
            if ($department !== '' && $department !== $dept) {
                continue;
            }
            $sections[$dept] = $rows->where('department', $dept)->values()->all();
        }
        if ($department === '' || $department === 'Uncategorized') {
            $sections['Uncategorized'] = $rows
                ->filter(fn ($r) => empty($r['department']))
                ->values()
                ->all();
        }

        return [$sections, $group, $dateFrom, $dateTo, $search];
    }

    private function buildEquipmentRows(Request $request): array
    {
        $status = trim((string) $request->query('status', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));
        $search = trim((string) $request->query('search', ''));

        $query = Equipment::orderBy('name');
        if ($status !== '') {
            $query->where('status', $status);
        }
        if ($dateFrom !== '') {
            $query->whereDate('date_acquired', '>=', $dateFrom);
        }
        if ($dateTo !== '') {
            $query->whereDate('date_acquired', '<=', $dateTo);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('brand', 'like', '%' . $search . '%')
                  ->orWhere('property_no', 'like', '%' . $search . '%');
            });
        }

        $rows = $query->get()->map(fn ($e) => [
            'name' => $e->name,
            'brand' => $e->brand,
            'property_no' => $e->property_no,
            'date_acquired' => $e->date_acquired,
            'cost' => (float) $e->cost,
            'status' => $e->status,
        ])->all();

        return [$rows, $status, $dateFrom, $dateTo, $search];
    }
}
