<?php
/**
 * ADD / EDIT BEAUTICIAN MODAL
 * ------------------------------------------------------------------
 * Purpose: Modal form for creating and updating beautician records,
 * styled consistently with clients and staff module modals.
 */
?>
<div class="modal fade" id="beauticianModal" tabindex="-1" aria-labelledby="beauticianModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="beauticianModalTitle">Add New Beautician</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="beauticianForm">
                    <input type="hidden" id="editBeauticianId">
                    
                    <div class="mb-3">
                        <label for="beauticianName" class="form-label field-label">Full Name</label>
                        <input type="text" class="form-control rounded-3" id="beauticianName" required placeholder="e.g. Maria Santos">
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="beauticianSpecialization" class="form-label field-label">Specialization</label>
                            <select class="form-select rounded-3" id="beauticianSpecialization" required>
                                <option value="Hair Specialist">Hair Specialist</option>
                                <option value="Makeup Artist">Makeup Artist</option>
                                <option value="Nail Artist">Nail Artist</option>
                                <option value="Esthetician">Esthetician</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="beauticianStatus" class="form-label field-label">Status</label>
                            <select class="form-select rounded-3" id="beauticianStatus" required>
                                <option value="Active">Active</option>
                                <option value="On Leave">On Leave</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="beauticianAvatarFile" class="form-label field-label">Profile Picture (Optional)</label>
                        <input type="file" class="form-control rounded-3" id="beauticianAvatarFile" accept="image/*">
                        <div class="form-text text-muted" style="font-size: 0.75rem;">Choose an image file from your device.</div>
                    </div>

                    <div class="mb-3">
                        <label for="beauticianEmail" class="form-label field-label">Email Address</label>
                        <input type="email" class="form-control rounded-3" id="beauticianEmail" required placeholder="e.g. maria@beautyreserve.com">
                    </div>

                    <div class="mb-3">
                        <label for="beauticianPhone" class="form-label field-label">Contact Number</label>
                        <input type="text" class="form-control rounded-3" id="beauticianPhone" required placeholder="e.g. 09123456789">
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4" id="saveBeauticianBtn">Add Beautician</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>