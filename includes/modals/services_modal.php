<!-- Add / Edit Service Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="serviceForm">
                    <input type="hidden" id="editServiceId">
                    
                    <div class="mb-3">
                        <label for="serviceName" class="form-label field-label">Service Name</label>
                        <input type="text" class="form-control rounded-3" id="serviceName" required placeholder="e.g. Deep Cleansing Facial">
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="category" class="form-label field-label">Category</label>
                            <select class="form-select rounded-3" id="category" required>
                                <option value="Hair">Hair</option>
                                <option value="Face">Face</option>
                                <option value="Nails">Nails</option>
                                <option value="Massage">Massage</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="duration" class="form-label field-label">Duration (Mins)</label>
                            <input type="number" class="form-control rounded-3" id="duration" required placeholder="60">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label field-label">Price (PHP)</label>
                        <input type="number" step="0.01" class="form-control rounded-3" id="price" required placeholder="2000.00">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label field-label">Description</label>
                        <textarea class="form-control rounded-3" id="description" rows="3" placeholder="Brief details about this service..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4" id="saveServiceBtn">Add Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View Service Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="viewModalTitle">Service Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <!-- Dynamically filled by JS -->
            </div>
        </div>
    </div>
</div>