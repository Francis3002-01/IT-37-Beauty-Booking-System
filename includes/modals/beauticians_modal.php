<?php
/**
 * BEAUTICIAN ADD / EDIT MODAL COMPONENT
 * ------------------------------------------------------------------
 * Purpose: Provides the modal dialog for registering or editing a 
 * beautician's credentials. Form inputs include 'name' attributes 
 * for seamless integration with PHP $_POST / Database endpoints.
 */
?>

<div class="modal fade" id="beauticianModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content beautician-modal-content">
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-1 align-items-start">
                <div>
                    <h4 class="modal-title fw-bold" id="modalTitle">Add New Beauticians</h4>
                    <p class="modal-subtitle text-muted mb-0">Enter core staff credentials for registration</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body Form -->
            <div class="modal-body pt-3">
                <form id="beauticianForm" method="POST" onsubmit="handleBeauticianSubmit(event)">
                    <!-- Hidden ID field for edit operations -->
                    <input type="hidden" id="editBeauticianId" name="beautician_id">

                    <!-- Row: Last Name & First Name -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="lastName" class="form-label beautician-label">Last Name</label>
                            <input type="text" class="form-control beautician-input" id="lastName" name="last_name" placeholder="e.g. John" required>
                        </div>
                        <div class="col-6">
                            <label for="firstName" class="form-label beautician-label">First Name</label>
                            <input type="text" class="form-control beautician-input" id="firstName" name="first_name" placeholder="e.g. John" required>
                        </div>
                    </div>

                    <!-- Specialty Type -->
                    <div class="mb-3">
                        <label for="resourceType" class="form-label beautician-label">Specialty Type</label>
                        <select class="form-select beautician-input" id="resourceType" name="specialty_type" required>
                            <option value="" disabled selected hidden>Makeup Artist</option>
                            <option value="Hair Stylist">Hair Stylist</option>
                            <option value="Makeup Artist">Makeup Artist</option>
                            <option value="Nail Specialist">Nail Specialist</option>
                        </select>
                    </div>

                    <!-- Contact No. -->
                    <div class="mb-3">
                        <label for="contactNo" class="form-label beautician-label">Contact No.</label>
                        <input type="text" class="form-control beautician-input" id="contactNo" name="contact_no" placeholder="+111 222 333 444" required>
                    </div>

                    <!-- Address -->
                    <div class="mb-4">
                        <label for="address" class="form-label beautician-label">Address</label>
                        <input type="text" class="form-control beautician-input" id="address" name="address" placeholder="Enter Address" required>
                    </div>

                    <!-- Form Action Buttons -->
                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <button type="button" class="btn btn-beautician-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-beautician-save" id="saveBeauticianBtn">Save Beautician</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>