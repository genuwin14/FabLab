# Report Generation Plan

Add admin-only report generation for inventory snapshots and equipment register, modeled on the PEDS Digital Customization Center sample document (`SampleFileInventory/Inventory_Fabrication-Laboratory Janaury 2024 (1).docx`).

## Decisions

- **Scope:** All three phases — inventory snapshot, equipment register, and Display/Sponsored/Damaged/Consumed tracking.
- **Tracking model:** Option A — add 4 columns directly to `products`, `raw_materials`, `textures`. (No movements log; report reflects "now" rather than historical "as of <date>".)
- **Build order:** Schema first, ship complete report once. No interim partial-column release.
- **Roles:** Admin only. Reports link to be **removed** from the staff sidebar.
- **Export formats:** PDF (`barryvdh/laravel-dompdf`) and DOCX (`phpoffice/phpword`). No Excel/CSV.
- **Folder pattern:** Continue clone-per-role — admin only, no shared report components.

## Sample report structure (target)

**Materials report** — 7 columns:
Item · Unit · No. of Units on Display · No. of Sponsored Units · No. of Damaged Units · No. of Units Consumed · Available Units for Production

**Equipment report** — 6 columns:
Machinery and Equipment · Brand · Property No. · Date Acquired · Cost · Status

---

## M1 — Dependencies ✅

- [x] `composer require barryvdh/laravel-dompdf` (v3.1.2)
- [x] `composer require phpoffice/phpword` (v1.4.0, pulled in `phpoffice/math` v0.3.0)
- [x] Verify both packages auto-register service providers (Laravel package discovery — DONE)

## M2 — Phase 3 schema (Display / Sponsored / Damaged / Consumed) ✅

- [x] Migration: add `units_on_display`, `units_sponsored`, `units_damaged`, `units_consumed` to `products` (integer, default 0)
- [x] Migration: add the same 4 columns to `raw_materials` (decimal 10,2 to match `stock_quantity`)
- [x] Migration: add the same 4 columns to `textures` (decimal 10,2)
- [x] Update `Product` model `$fillable`
- [x] Update `RawMaterial` model `$fillable`
- [x] Update `Texture` model `$fillable`
- [x] Run migrations (`2026_05_08_000001_add_inventory_tracking_columns_for_reports`)

## M3 — Wire new fields into existing CRUD forms ✅

Without this, the new columns can never be populated from the UI.

- [x] `admin/product/components/modal-add-product.blade.php` + `modal-edit-product.blade.php` — 4 new inputs in "Report Tracking" section
- [x] `Admin/ProductController` validation rules
- [x] `admin/product/products.blade.php` JS — populate new fields on edit modal open
- [x] `admin/raw-materials/components/modal-add.blade.php` + `modal-edit.blade.php` — 4 new inputs
- [x] `Admin/RawMaterialController` validation
- [x] `admin/raw-materials/index.blade.php` — `data-units_*` attributes + JS populate
- [x] `admin/textures/components/modal-add.blade.php` + `modal-edit.blade.php` — 4 new inputs
- [x] `Admin/TextureController` validation
- [x] `admin/textures/index.blade.php` — `data-units_*` attributes + JS populate
- [ ] Manual test: edit one product and confirm values persist (deferred to M7)

## M4 — Phase 2: Equipment register ✅

### Schema & model
- [x] Migration: create `equipment` table (added `notes` column too)
- [x] `app/Models/Equipment.php` with `$primaryKey = 'equipment_id'`, `SoftDeletes`, casts

### Admin CRUD
- [x] `Admin/EquipmentController` — `index`, `store`, `update`, `destroy`
- [x] `admin/equipment/index.blade.php`
- [x] `admin/equipment/components/modal-add.blade.php`
- [x] `admin/equipment/components/modal-edit.blade.php`
- [x] `admin/equipment/components/modal-delete.blade.php`
- [x] Routes: `admin.equipment.{index,store,update,destroy}` in `routes/web.php`
- [x] Sidebar entry under "Inventory Control" with `bi-tools` icon

## M5 — Report controller and views (Phase 1 + Phase 2 reports) ✅

### Controller
- [x] `Admin/ReportController` with all 7 methods (`index`, `materials`, `equipment`, `exportMaterialsPdf`, `exportMaterialsDocx`, `exportEquipmentPdf`, `exportEquipmentDocx`)

### Views
- [x] `admin/reports/index.blade.php` — hub page with two cards
- [x] `admin/reports/materials.blade.php` — on-screen 8-column table (added Type column) + Export PDF/Word buttons + group filter
- [x] `admin/reports/equipment.blade.php` — on-screen 6-column table + Export buttons + status filter
- [x] `admin/reports/pdf/materials.blade.php` — dompdf landscape template with type badges + "As of" date
- [x] `admin/reports/pdf/equipment.blade.php` — dompdf landscape template with status badges

### DOCX generation
- [x] `app/Services/Reports/MaterialsDocxGenerator.php` (PhpWord, landscape, dark header row)
- [x] `app/Services/Reports/EquipmentDocxGenerator.php`

### Routes (all registered in `routes/web.php`)
- [x] `admin.reports.index` (GET)
- [x] `admin.reports.materials` (GET)
- [x] `admin.reports.equipment` (GET)
- [x] `admin.reports.materials.pdf` (GET)
- [x] `admin.reports.materials.docx` (GET)
- [x] `admin.reports.equipment.pdf` (GET)
- [x] `admin.reports.equipment.docx` (GET)

### Smoke tests (via tinker)
- [x] `buildMaterialsRows` returns 18 rows with all 4 tracking fields
- [x] DOCX generator produces valid 8.5 KB file
- [x] PDF renderer produces valid 1.2 MB file via dompdf

## M6 — Sidebar wiring ✅

- [x] `admin/partials/sidebar.blade.php` — Reports link wired to `admin.reports.index` with active-state check
- [x] `staff/partials/sidebar.blade.php` — entire "Administration" section removed

## M7 — Verification

- [ ] Manual test: open `/admin/reports` — hub renders with both report cards
- [ ] Manual test: open Materials report — filters work, table shows all 7 columns populated correctly
- [ ] Manual test: export Materials report as PDF — layout matches sample docx structure
- [ ] Manual test: export Materials report as DOCX — opens in Word, table is editable
- [ ] Manual test: equipment CRUD flow + Equipment report PDF/DOCX
- [ ] Manual test: log in as staff — confirm Reports link no longer visible

## M8 — Seeders ✅

- [x] `ProductSeeder` — added varied tracking values across all 8 products
- [x] `RawMaterialSeeder` — added tracking values across all 5 materials
- [x] `TextureSeeder` — added tracking values across all 5 textures
- [x] **`EquipmentSeeder` (NEW)** — 30 items derived directly from sample docx (19 machinery + 11 hand tools, mix of Serviceable/Non-Serviceable/Functional/Returned-for-repair)
- [x] `DatabaseSeeder` — registered `EquipmentSeeder`
- [x] `migrate:fresh --seed` ran clean (Equipment: 30, Products: 8, Raw Materials: 5, Textures: 5)

---

## M9 — Department-grouped sections (matches sample docx structure) ✅

The sample docx's "Inventory of Materials" splits into three PEDS sub-sections, not by entity type. Refactored the report to match.

- [x] `app/Enums/Department.php` — backed string enum (`DigitalCustomizationCenter`, `BookProduction`, `Woodworks`)
- [x] Migration `2026_05_08_000003_add_department_to_inventory_tables` — nullable `department` column on products / raw_materials / textures
- [x] Updated `$fillable` on Product, RawMaterial, Texture
- [x] Validation rules added to all three Admin controllers (`in:` enum values, nullable)
- [x] Department dropdown added to all 6 modals (add + edit × 3 entities) with "— Uncategorized —" option
- [x] Edit modal JS populates the dropdown from data attributes
- [x] Seeders updated:
  - 5 products → Digital Customization Center, 2 → Woodworks, 1 → Uncategorized
  - 3 raw materials → Woodworks, 2 NEW (Bond Paper + Vellum Board) → Book Production, 1 → Uncategorized
  - 4 textures → Digital Customization Center, 1 → Uncategorized
- [x] `ReportController::buildMaterialsSections` returns `[department => rows]` instead of flat array
- [x] On-screen view (`admin/reports/materials.blade.php`) renders one card per section with item count badges
- [x] PDF view (`admin/reports/pdf/materials.blade.php`) renders one banner-headed table per section, page-break-friendly
- [x] `MaterialsDocxGenerator` emits one heading + one table per section (replicates the sample docx layout)
- [x] Smoke test post-`migrate:fresh --seed`: 4 sections populated (DCC: 9, Book: 2, Woodworks: 6, Uncategorized: 3); DOCX 8.8KB; PDF 1.3MB

## Out of scope (deferred)

- Historical "As of <date>" snapshots (would require Option B movements log)
- Excel/CSV exports
- Per-department/category sub-reports (sample docx splits by Digital Customization / Book Production / Woodworks — current schema has `categories` but no sub-unit grouping)
- Staff-side reports
- Scheduled / emailed reports
