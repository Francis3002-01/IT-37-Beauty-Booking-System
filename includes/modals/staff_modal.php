<!-- ADD / EDIT STAFF MODAL: ordinary POST form matching staff table -->
<div
    class="modal fade"
    id="staffModal"
    tabindex="-1"
    aria-labelledby="staffModalTitle"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="staffModalTitle">Add New Staff</h5>
            </div>
            <div class="modal-body">
                <form
                    id="staffForm"
                    method="POST"
                    enctype="multipart/form-data"
                    action="backend%20logic/staff_backend.php"
                    onsubmit="return validateStaffForm();"
                >
                    <div class="mb-3">
                        <label for="staffImage" class="form-label field-label"
                            >Profile Picture (Optional)</label
                        >
                        <input
                            type="file"
                            class="form-control"
                            id="staffImage"
                            name="profileImage"
                            accept="image/jpeg,image/png,image/webp"
                            aria-describedby="staffImageHelp"
                        />
                        <div id="staffImageHelp" class="form-text">
                            JPG, PNG or WebP, up to 2 MB.
                        </div>
                    </div>
                    <input type="hidden" id="editStaffId" name="staffID" />
                    <input type="hidden" id="staffAction" name="action" value="create" />

                    <div class="mb-3">
                        <label for="staffFirstName" class="form-label field-label"
                            >First Name</label
                        >
                        <input
                            type="text"
                            class="form-control rounded-3"
                            id="staffFirstName"
                            name="firstName"
                            maxlength="50"
                            required
                            oninput="this.setCustomValidity('')"
                        />
                    </div>
                    <div class="mb-3">
                        <label for="staffLastName" class="form-label field-label">Last Name</label>
                        <input
                            type="text"
                            class="form-control rounded-3"
                            id="staffLastName"
                            name="lastName"
                            maxlength="50"
                            required
                            oninput="this.setCustomValidity('')"
                        />
                    </div>

                    <!-- NEW: Username Field for User Account Creation -->
                    <div class="mb-3" id="usernameContainer">
                        <label for="staffUsername" class="form-label field-label">Username</label>
                        <input
                            type="text"
                            class="form-control rounded-3"
                            id="staffUsername"
                            name="username"
                            maxlength="50"
                            placeholder="Enter username for login"
                            autocomplete="off"
                            required
                        />
                        <div class="form-text">
                            A secure random password will be auto-generated for this account.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="staffAddress" class="form-label field-label">Address</label>
                        <textarea
                            class="form-control rounded-3"
                            id="staffAddress"
                            name="address"
                            maxlength="255"
                            rows="2"
                            required
                            oninput="this.setCustomValidity('')"
                        ></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="staffContactNo" class="form-label field-label"
                            >Contact Number</label
                        >
                        <input
                            type="text"
                            class="form-control rounded-3"
                            id="staffContactNo"
                            name="contactNo"
                            maxlength="11"
                            inputmode="numeric"
                            pattern="[0-9]{11}"
                            minlength="11"
                            required
                            aria-describedby="staffContactHelp"
                            oninput="this.setCustomValidity('')"
                        />
                        <div id="staffContactHelp" class="form-text">Enter exactly 11 digits.</div>
                    </div>
                    <div class="mb-3">
                        <label for="staffType" class="form-label field-label">Role</label
                        ><select
                            class="form-select rounded-3"
                            id="staffType"
                            name="staffType"
                            required
                        >
                            <option value="Receptionist">Receptionist</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="staffStatus" class="form-label field-label"
                            >Employment Status</label
                        ><select
                            class="form-select rounded-3"
                            id="staffStatus"
                            name="employmentStatus"
                            required
                            onchange="updateSeparationRequirement()"
                        >
                            <option value="Active">Active</option>
                            <option value="On Leave">On Leave</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="dateJoined" class="form-label field-label">Date Joined</label
                        ><input
                            type="date"
                            class="form-control rounded-3"
                            id="dateJoined"
                            name="dateJoined"
                            required
                            oninput="document.getElementById('dateSeparated').setCustomValidity('')"
                        />
                    </div>
                    <div class="mb-3">
                        <label
                            for="dateSeparated"
                            id="dateSeparatedLabel"
                            class="form-label field-label"
                            >Date Separated (Inactive only)</label
                        ><input
                            type="date"
                            class="form-control rounded-3"
                            id="dateSeparated"
                            name="dateSeparated"
                            disabled
                            oninput="this.setCustomValidity('')"
                        />
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button
                            type="button"
                            class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal"
                        >
                            Cancel</button
                        ><button
                            type="submit"
                            class="btn btn-dark rounded-pill px-4"
                            id="saveStaffBtn"
                        >
                            Add Staff
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- DELETE CONFIRMATION MODAL -->
<div
    class="modal fade"
    id="deleteStaffModal"
    tabindex="-1"
    aria-labelledby="deleteStaffModalTitle"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" id="deleteStaffModalTitle">
                    Confirm Deletion
                </h5>
            </div>
            <div class="modal-body py-3">
                <p class="mb-0 text-secondary">
                    Are you sure you want to delete this staff member? Records used in appointments
                    cannot be deleted.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button
                    type="button"
                    class="btn btn-light rounded-pill px-4"
                    data-bs-dismiss="modal"
                >
                    Cancel
                </button>
                <form
                    id="deleteStaffForm"
                    method="POST"
                    action="backend%20logic/staff_backend.php"
                    class="d-inline"
                >
                    <input type="hidden" name="action" value="delete" />
                    <input type="hidden" name="staffID" id="deleteStaffId" />
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- VIEW STAFF MODAL -->
<div
    class="modal fade"
    id="viewStaffModal"
    tabindex="-1"
    aria-labelledby="viewStaffModalTitle"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="viewStaffModalTitle">
                    Staff Details (#<span id="viewStaffId"></span>)
                </h5>
            </div>
            <div class="modal-body">
                <!-- Profile Picture Preview -->
                <div class="text-center mb-4">
                    <img
                        id="viewStaffImage"
                        src=""
                        alt="Profile Picture"
                        class="rounded-circle object-fit-cover shadow-sm"
                        width="90"
                        height="90"
                    />
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <span class="text-muted small d-block">First Name</span>
                        <span id="viewStaffFirstName" class="fw-semibold"></span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Last Name</span>
                        <span id="viewStaffLastName" class="fw-semibold"></span>
                    </div>
                    <div class="col-12">
                        <span class="text-muted small d-block">Role</span>
                        <span id="viewStaffType" class="fw-semibold"></span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Contact Number</span>
                        <span id="viewStaffContactNo" class="fw-semibold"></span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Employment Status</span>
                        <span id="viewStaffStatus" class="fw-semibold"></span>
                    </div>
                    <div class="col-12">
                        <span class="text-muted small d-block">Address</span>
                        <span id="viewStaffAddress" class="fw-semibold"></span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Date Joined</span>
                        <span id="viewStaffDateJoined" class="fw-semibold"></span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Date Separated</span>
                        <span id="viewStaffDateSeparated" class="fw-semibold">-</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-3">
                <button
                    type="button"
                    class="btn btn-dark rounded-pill px-4 w-100"
                    data-bs-dismiss="modal"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
