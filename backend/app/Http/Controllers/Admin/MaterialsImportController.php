<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\Reports\Import\MaterialsDocxParser;
use App\Services\Reports\Import\MaterialsImportApplier;
use App\Services\Reports\Import\MaterialsImportPlanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

/**
 * Importing an old Inventory of Materials report back into the system.
 *
 * Upload, review, confirm — deliberately three steps. An old report restates
 * stock figures wholesale, so the admin sees every row and what it would change
 * before anything is written.
 *
 * Between the steps the session carries the parsed rows, not the uploaded file
 * and not the plan. Rows are what the document said and cannot go stale; the
 * plan is rebuilt on each request so the preview and the write are both
 * measured against the database as it stands at that moment, and a row someone
 * else edited in between shows up rather than being silently overwritten with
 * figures worked out minutes earlier.
 */
class MaterialsImportController extends Controller
{
    private const SESSION_KEY = 'materials_import.rows';
    private const FILENAME_KEY = 'materials_import.filename';

    public function __construct(
        private MaterialsDocxParser $parser,
        private MaterialsImportPlanner $planner,
        private MaterialsImportApplier $applier,
    ) {}

    /**
     * Step 1 — read the upload and hold what it said.
     */
    public function store(Request $request)
    {
        // Flashed rather than thrown as validation errors: the form lives in a
        // modal, and a redirect closes it, so a field error would land on a
        // panel nobody is looking at. The layout shows flashed messages as a
        // toast, which survives the redirect.
        $validator = Validator::make($request->all(), [
            'report' => ['required', 'file', 'max:10240'],
        ], [
            'report.required' => 'Choose a report file to import.',
            'report.max' => 'That report is larger than 10MB.',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first('report'));
        }

        $file = $request->file('report');

        // Extension rather than MIME: a .docx is a zip, and PHP reports it as
        // one or the other depending on the platform, so a MIME rule rejects
        // legitimate files on some machines and passes .doc on none.
        if (strtolower($file->getClientOriginalExtension()) !== 'docx') {
            return back()->with('error', 'Upload a Word .docx report. A legacy .doc has to be saved as .docx first — its tables cannot be read.');
        }

        try {
            $result = $this->parser->parse($file->getRealPath());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $request->session()->put(self::SESSION_KEY, $result['rows']);
        $request->session()->put(self::FILENAME_KEY, $file->getClientOriginalName());

        return redirect()
            ->route('admin.reports.materials.import.preview')
            ->with('parse_warnings', $result['warnings']);
    }

    /**
     * Step 2 — show what applying it would do.
     */
    public function preview(Request $request)
    {
        $rows = $request->session()->get(self::SESSION_KEY);

        if (! $rows) {
            return redirect()
                ->route('admin.reports.materials')
                ->with('error', 'That import is no longer held. Upload the report again.');
        }

        $plan = $this->planner->plan($rows);

        return view('admin.reports.import-preview', [
            'items' => $plan['items'],
            'summary' => $plan['summary'],
            'filename' => $request->session()->get(self::FILENAME_KEY, 'report.docx'),
            'warnings' => $request->session()->get('parse_warnings', []),
            // Creating an item needs a supplier: the column is a required
            // foreign key and the materials list reads through it unguarded.
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    /**
     * Step 3 — write it.
     */
    public function confirm(Request $request)
    {
        $rows = $request->session()->get(self::SESSION_KEY);

        if (! $rows) {
            return redirect()
                ->route('admin.reports.materials')
                ->with('error', 'That import is no longer held. Upload the report again.');
        }

        $filename = $request->session()->get(self::FILENAME_KEY, 'report.docx');

        $validator = Validator::make($request->all(), [
            'create' => ['sometimes', 'array'],
            'create.*' => ['integer', 'min:0'],
            // Required only when something is actually being created, so an
            // import that just updates existing items never asks for one.
            'supplier_id' => ['required_with:create', 'nullable', 'integer', 'exists:suppliers,supplier_id'],
        ], [
            'supplier_id.required_with' => 'Choose which supplier the new items should be filed under.',
            'supplier_id.exists' => 'That supplier no longer exists.',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        $indexes = array_map('intval', $request->input('create', []));

        $result = $this->applier->apply(
            plan: $this->planner->plan($rows),
            context: [
                'user_id' => Auth::id(),
                'note' => 'Imported from ' . $filename,
            ],
            create: $indexes === [] ? null : [
                'indexes' => $indexes,
                'supplier_id' => (int) $request->input('supplier_id'),
            ],
        );

        $request->session()->forget([self::SESSION_KEY, self::FILENAME_KEY]);

        return redirect()
            ->route('admin.reports.materials')
            ->with('import_result', $result)
            ->with('success', $this->summaryLine($result));
    }

    /**
     * Step 2, cancelled.
     */
    public function discard(Request $request)
    {
        $request->session()->forget([self::SESSION_KEY, self::FILENAME_KEY]);

        return redirect()->route('admin.reports.materials');
    }

    /**
     * @param  array{applied: int, created: int, skipped: int, failed: list<array{name: string, reason: string}>}  $result
     */
    private function summaryLine(array $result): string
    {
        $parts = [];

        if ($result['applied'] > 0) {
            $parts[] = sprintf('%d %s updated', $result['applied'], $result['applied'] === 1 ? 'item' : 'items');
        }

        if ($result['created'] > 0) {
            $parts[] = sprintf('%d %s created', $result['created'], $result['created'] === 1 ? 'item' : 'items');
        }

        $line = $parts === []
            ? 'Nothing was changed by that report.'
            : ucfirst(implode(' and ', $parts)) . ' from the report.';

        if ($result['failed'] !== []) {
            $line .= sprintf(' %d could not be applied.', count($result['failed']));
        }

        return $line;
    }
}
