<?php
/**
 * APPOINTMENT MODAL DIALOGS
 * ------------------------------------------------------------------
 * Purpose: Holds popup modal windows for Creating, Editing, Viewing, and Deleting appointments.
 * Why include this? 
 * 1. Prevents appointments.php from getting cluttered with hundreds of lines of form HTML.
 * 2. Keeps UI dialog component logic isolated in its own subfolder (modals/).
 */
?>

<!-- Create / Edit Appointment Modal (2-Column Form Layout) -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-4">
            <h3 class="fw-bold mb-4" id="modalTitle">Create New Appointment</h3>
            <form id="appointmentForm" onsubmit="handleFormSubmit(event)">
                <input type="hidden" id="editAppointmentId">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="field-label">Last Name</label>
                            <input type="text" class="form-control" id="clientLastName" placeholder="Enter Last Name" required>
                        </div>
                        <div class="mb-3">
                            <label class="field-label">First Name</label>
                            <input type="text" class="form-control" id="clientFirstName" placeholder="Enter First Name" required>
                        </div>
                        <div class="mb-3">
                            <label class="field-label">Address</label>
                            <input type="text" class="form-control" id="clientAddress" placeholder="Enter Address" required>
                        </div>
                        <div class="mb-3">
                            <label class="field-label">Contact No.</label>
                            <input type="text" class="form-control" id="clientContactNo" placeholder="Enter Contact No." required>
                        </div>
                        <div class="mb-3">
                            <label class="field-label">Appointment Type</label>
                            <select class="form-select" id="appointmentType" onchange="toggleVenueInput()" required>
                                <option value="Salon service">Salon service</option>
                                <option value="Home service">Home service</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="field-label">Appointment Notes</label>
                            <textarea class="form-control" id="appointmentNotes" rows="4" placeholder="Special instructions, allergies, or directions..."></textarea>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="field-label">Service</label>
                            <select class="form-select" id="serviceSelect" onchange="updateServiceCost()" required></select>
                        </div>
                        <div class="mb-3">
                            <label class="field-label">Resource (Beautician)</label>
                            <select class="form-select" id="resourceSelect" onchange="validateAvailability()" required></select>
                        </div>
                        <div class="mb-3">
                            <label class="field-label">Date</label>
                            <input type="date" class="form-control" id="appointmentDate" onchange="validateAvailability()" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="field-label">Start Time</label>
                                <input type="time" class="form-control" id="startTime" onchange="validateAvailability()" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="field-label">End Time</label>
                                <input type="time" class="form-control" id="endTime" onchange="validateAvailability()" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div id="conflictBanner" class="p-2 rounded text-center fw-bold" style="font-size: 0.85rem; background-color: #e6fcf5; color: #0ca678;">
                                Conflict Check: No Overlaps: Clear
                            </div>
                        </div>
                        <div class="mb-3" id="venueWrapper" style="display: none;">
                            <label class="field-label">Venue (Required for Home Service)</label>
                            <input type="text" class="form-control" id="venueInput" placeholder="Enter home service address">
                        </div>
                        <div class="mb-3">
                            <label class="field-label">Payment Method</label>
                            <select class="form-select" id="paymentMethod" required>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="E-Wallet">E-Wallet</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="field-label">Payment Status</label>
                            <select class="form-select" id="paymentStatus" required>
                                <option value="Unpaid">Unpaid</option>
                                <option value="Paid">Paid</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-create px-4" id="saveAppointmentBtn">Create Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <h3 class="fw-bold mb-3" id="viewModalTitle">Appointment Details</h3>
            <div id="viewModalBody"></div>
            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-dark rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Delete Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content p-4 text-center">
            <h5 class="fw-bold mb-2">Delete Appointment?</h5>
            <p class="text-muted mb-4" style="font-size: 0.875rem;">Are you sure you want to delete <span id="deleteAppointmentIdText" class="fw-bold text-dark"></span>? This action cannot be undone.</p>
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-light px-3 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger px-3 rounded-pill" onclick="executeDeleteAppointment()">Delete</button>
            </div>
        </div>
    </div>
</div>