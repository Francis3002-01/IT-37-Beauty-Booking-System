<!-- Add / Edit Client Modal -->
<div class="modal fade" id="clientModal" tabindex="-1" aria-labelledby="clientModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="clientModalTitle">Add New Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="clientForm">
                    <input type="hidden" id="editClientId">
                    
                    <div class="mb-3">
                        <label for="clientName" class="form-label field-label">Full Name</label>
                        <input type="text" class="form-control rounded-3" id="clientName" required placeholder="e.g. Maria Santos">
                    </div>

                    <div class="mb-3">
                        <label for="clientEmail" class="form-label field-label">Email Address</label>
                        <input type="email" class="form-control rounded-3" id="clientEmail" required placeholder="e.g. maria@example.com">
                    </div>

                    <div class="mb-3">
                        <label for="clientPhone" class="form-label field-label">Contact Number</label>
                        <input type="text" class="form-control rounded-3" id="clientPhone" required placeholder="e.g. 09123456789">
                    </div>

                    <div class="mb-3">
                        <label for="clientAddress" class="form-label field-label">Address</label>
                        <textarea class="form-control rounded-3" id="clientAddress" rows="2" placeholder="e.g. Dumaguete City, Negros Oriental"></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4" id="saveClientBtn">Add Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>