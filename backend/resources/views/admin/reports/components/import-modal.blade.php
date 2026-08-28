{{--
    Upload step for importing an old Inventory of Materials report.

    It asks for .docx and nothing else, and says why on the form rather than
    only failing afterwards: a .docx is OOXML, so its tables come back as real
    rows and cells, while the legacy binary .doc loses the grid entirely and
    would leave the importer guessing which figure belonged to which column.

    Styles are local to this file so it can sit on the reports page without
    borrowing the order pages' modal theme, which is defined over there.
--}}
<div class="modal fade report-import-modal" id="importReportModal" tabindex="-1"
    aria-labelledby="importReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="report-import-header d-flex align-items-center gap-3">
                <div class="report-import-icon flex-shrink-0">
                    <i class="bi bi-upload"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="report-import-eyebrow">Inventory of Materials</div>
                    <h5 class="modal-title fw-bold mb-0 text-white" id="importReportModalLabel">Import an Old Report</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('admin.reports.materials.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Reads the inventory tables out of a previous report and shows you what it would change.
                        Nothing is saved until you have reviewed it.
                    </p>

                    <label for="reportFile" class="form-label fw-semibold text-uppercase text-muted"
                        style="letter-spacing: 0.04em; font-size: 0.7rem;">Report file</label>
                    <input type="file" name="report" id="reportFile" accept=".docx" required
                        class="form-control report-import-input @error('report') is-invalid @enderror">
                    @error('report')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <div class="d-flex gap-2 mt-3 p-3 rounded-3" style="background-color: rgba(255, 193, 7, 0.12);">
                        <i class="bi bi-info-circle flex-shrink-0" style="color: #997404;"></i>
                        <div class="small" style="color: #6c5200;">
                            Word <strong>.docx</strong> only. An older <strong>.doc</strong> keeps its text but not
                            its table grid, so open it in Word and use <em>Save As → .docx</em> first. A PDF export
                            cannot be read back at all.
                        </div>
                    </div>
                </div>

                <div class="report-import-footer">
                    <button type="button" class="btn btn-light border fw-semibold rounded-2 px-4" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn fw-semibold rounded-2 px-4 report-import-submit">
                        <i class="bi bi-search me-2"></i>Read Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .report-import-modal .modal-content { border-radius: 18px; }

    .report-import-header {
        background: linear-gradient(135deg, #05111a 0%, #0e2e45 100%);
        padding: 18px 24px;
    }
    .report-import-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        background-color: rgba(255, 197, 8, 0.15);
        color: #ffc508;
    }
    .report-import-eyebrow {
        font-size: 0.68rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(255, 197, 8, 0.85);
    }

    .report-import-input {
        background-color: #f8f9fa;
        border: 1px solid transparent;
        border-radius: 10px;
        padding: 0.6rem 0.85rem;
        transition: all 0.2s ease;
    }
    .report-import-input:focus {
        background-color: #fff;
        border-color: #ffc508;
        box-shadow: 0 0 0 3px rgba(255, 197, 8, 0.12);
    }

    .report-import-footer {
        background-color: #fff;
        padding: 16px 24px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .report-import-submit {
        background-color: #0e2e45;
        border: 1px solid #0e2e45;
        color: #fff;
        transition: all 0.2s ease;
    }
    .report-import-submit:hover {
        background-color: #ffc508;
        border-color: #ffc508;
        color: #0e2e45;
    }
</style>
