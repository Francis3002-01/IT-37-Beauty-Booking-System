<?php
/**
 * ADD / EDIT STAFF MODAL (`includes/modals/staff_modal.php`)
 * ------------------------------------------------------------------
 * Purpose: Modal form fields with local file upload for profile pictures.
 */
?>
<div class="modal fade" id="staffModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Add New Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="staffForm">
                    <input type="hidden" id="editStaffId">
                    
                    <div class="mb-3">
                        <label for="staffName" class="form-label field-label">Full Name</label>
                        <input type="text" class="form-control rounded-3" id="staffName" required placeholder="e.g. Elena Rostova">
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="role" class="form-label field-label">Role / Specialty</label>
                            <select class="form-select rounded-3" id="role" required>
                                <option value="Hair stylist">Hair stylist</option>
                                <option value="Hair Stylist">Hair Stylist</option>
                                <option value="Makeup Artist">Makeup Artist</option>
                                <option value="Nail Specialist">Nail Specialist</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="status" class="form-label field-label">Status</label>
                            <select class="form-select rounded-3" id="status" required>
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="ON LEAVE">ON LEAVE</option>
                                <option value="INACTIVE">INACTIVE</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="avatarFile" class="form-label field-label">Profile Picture (Optional)</label>
                        <input type="file" class="form-control rounded-3" id="avatarFile" accept="image/*">
                        <div class="form-text text-muted" style="font-size: 0.75rem;">Choose an image file from your device.</div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label field-label">Address</label>
                        <input type="text" class="form-control rounded-3" id="address" required placeholder="e.g. 124 Grand Ave, Suite 3B">
                    </div>

                    <div class="mb-3">
                        <label for="contactNumber" class="form-label field-label">Contact Number</label>
                        <input type="text" class="form-control rounded-3" id="contactNumber" required placeholder="e.g. +1 (555) 234-5678">
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4" id="saveStaffBtn">Add Staff</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>