{{--
    Confirm undoing a ledger entry. Caller passes $routePrefix.

    Reversing writes an opposite entry rather than deleting the original, so
    both the mistake and the fix stay visible in the log.
--}}
<div class="modal fade usage-reverse-modal" id="reverseMovementModal" tabindex="-1"
    aria-labelledby="reverseMovementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered usage-reverse-modal-dialog">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="usage-reverse-modal-header">
                <div class="usage-reverse-modal-icon">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </div>
                <h5 class="modal-title fw-bold mb-1 text-white" id="reverseMovementModalLabel">Reverse this entry?</h5>
                <p class="mb-0" style="color: rgba(255, 255, 255, 0.6); font-size: 0.8rem;">
                    The original stays in the log
                </p>
            </div>

            <div class="usage-reverse-modal-body p-4 text-center">
                <p class="text-muted mb-3" style="font-size: 0.88rem;">
                    <span class="fw-bold text-dark" id="reverseMovementSummary">—</span>
                    will be put back and the entry marked as reversed.
                </p>
                <p class="text-muted small mb-0">
                    Use this when a movement was recorded in error. To record fresh usage, close this and use
                    <span class="fw-semibold">Record Usage</span> instead.
                </p>
            </div>

            <div class="usage-reverse-modal-footer">
                <button type="button" class="btn usage-reverse-cancel-btn rounded-pill px-4" data-bs-dismiss="modal">
                    Cancel
                </button>
                <form id="reverseMovementForm" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn usage-reverse-confirm-btn rounded-pill px-4">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reverse
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var ROUTE_PREFIX = @json($routePrefix);

        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('reverseMovementModal');
            if (!modal) return;

            modal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                if (!button) return;

                document.getElementById('reverseMovementForm').action =
                    '/' + ROUTE_PREFIX + '/raw-material-movements/' + button.getAttribute('data-id') + '/reverse';

                document.getElementById('reverseMovementSummary').textContent =
                    button.getAttribute('data-quantity') + ' ' + button.getAttribute('data-unit')
                    + ' of ' + button.getAttribute('data-material')
                    + ' (' + button.getAttribute('data-reason').toLowerCase() + ')';
            });
        });
    })();
</script>
