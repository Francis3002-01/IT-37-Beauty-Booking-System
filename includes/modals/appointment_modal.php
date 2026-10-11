<?php
/**
 * APPOINTMENT MODAL DIALOGS
 * ------------------------------------------------------------------
 * Purpose: Holds popup modal windows for Creating, Editing, Viewing, and Deleting appointments.
 */
?>

<!-- Create / Edit Appointment Modal (2-Column Form Layout) -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-4">
            <h3 class="fw-bold mb-4" id="modalTitle">Create New Appointment</h3>
            <form id="appointmentForm" action="backend logic/appointment_backend.php" method="POST">
                <!-- Hidden ID for Editing & Action Type -->
                <input type="hidden" id="editAppointmentId" name="editAppointmentId" value="">
                <input type="hidden" name="action" value="create">

                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="field-label">Select Client</label>
                            <select class="form-select" id="clientSelect" name="clientID" onchange="fillClientDetails()" required>
                                <option value="" disabled selected>Select a Client...</option>
                                <!-- Populated dynamically by JS -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="field-label">Last Name</label>
                            <input type="text" id="clientLastName"
       name="clientLastName"
       class="form-control"
       placeholder="Last Name"
       readonly required>
                        </div>
                        <div class="mb-3">
                            <label class="field-label">First Name</label>
                            <input type="text" class="form-control" id="clientFirstName" name="clientFirstName" placeholder="First Name" disabled required>
                        </div>
                        <div class="mb-3">
                            <label class="field-label">Address</label>
                            <input type="text" class="form-control" id="clientAddress" name="clientAddress" placeholder="Address" disabled required>
                        </div>
                        <div class="mb-3">
                            <label class="field-label">Contact No.</label>
                            <input type="text" class="form-control" id="clientContactNo" name="clientContactNo" placeholder="Contact No." disabled required>
                        </div>
                        <div class="mb-3">
                            <label class="field-label">Appointment Type</label>
                            <select class="form-select" id="appointmentType" name="appointmentType" onchange="toggleVenueInput()" required>
                                <option value="Salon service">Salon service</option>
                                <option value="Home service">Home service</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="field-label">Appointment Notes</label>
                            <textarea class="form-control" id="appointmentNotes" name="notes" rows="4" placeholder="Special instructions, allergies, or directions..."></textarea>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="field-label">Service</label>
                            <select class="form-select" id="serviceSelect" name="serviceID" onchange="updateServiceCost()" required>
                                <option value="" disabled selected>Select a Service...</option>
                                <!-- Populated dynamically by JS -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="field-label">Resource (Beautician)</label>
                            <select class="form-select" id="resourceSelect" name="beauticianID" onchange="validateAvailability()" required>
                                <option value="" disabled selected>Select a Beautician...</option>
                                <!-- Populated dynamically by JS -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="field-label">Date</label>
                            <input type="date" class="form-control" id="appointmentDate" name="appointmentDate" onchange="validateAvailability()" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="field-label">Start Time</label>
                                <input type="time" class="form-control" id="startTime" name="startTime" onchange="validateAvailability()" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="field-label">End Time</label>
                                <input type="time" class="form-control" id="endTime" name="endTime" onchange="validateAvailability()" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div id="conflictBanner" class="p-2 rounded text-center fw-bold" style="font-size: 0.85rem; background-color: #e6fcf5; color: #0ca678;">
                                Conflict Check: No Overlaps: Clear
                            </div>
                        </div>
                        <div class="mb-3" id="venueWrapper" style="display: none;">
                            <label class="field-label">Venue (Required for Home Service)</label>
                            <input type="text" class="form-control" id="venueInput" name="venue" placeholder="Enter home service address">
                        </div>
                        <div class="mb-3">
                            <label class="field-label">Payment Method</label>
                            <select class="form-select" id="paymentMethod" name="paymentMethod" required>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="E-Wallet">E-Wallet</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="field-label">Payment Status</label>
                            <select class="form-select" id="paymentStatus" name="paymentStatus" required>
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