{{-- Shared confirm dialog — open via AdminConfirm() or [data-confirm] --}}
<div class="modal-backdrop" id="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title" id="confirm-modal-title" data-confirm-title>Are you sure?</h2>
            <button type="button" class="btn btn-ghost btn-sm btn-icon" data-confirm-cancel aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="mb-0" data-confirm-message>This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-confirm-cancel>Cancel</button>
            <button type="button" class="btn btn-primary" data-confirm-ok>Confirm</button>
        </div>
    </div>
</div>
