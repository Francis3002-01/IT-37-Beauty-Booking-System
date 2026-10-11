<!-- ADD / EDIT STAFF MODAL: ordinary POST form matching staff table -->
<div class="modal fade" id="staffModal" tabindex="-1" aria-labelledby="staffModalTitle" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered"><div class="modal-content p-3">
  <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold" id="staffModalTitle">Add New Staff</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <div class="modal-body">
   <form id="staffForm" method="POST" action="backend%20logic/staff_backend.php" onsubmit="return validateStaffForm()">
    <input type="hidden" id="editStaffId" name="staffID">
    <input type="hidden" id="staffAction" name="action" value="create">
    <div class="mb-3"><label for="staffFirstName" class="form-label field-label">First Name</label>
     <input type="text" class="form-control rounded-3" id="staffFirstName" name="firstName" maxlength="50" required oninput="this.setCustomValidity('')"></div>
    <div class="mb-3"><label for="staffLastName" class="form-label field-label">Last Name</label>
     <input type="text" class="form-control rounded-3" id="staffLastName" name="lastName" maxlength="50" required oninput="this.setCustomValidity('')"></div>
    <div class="mb-3"><label for="staffAddress" class="form-label field-label">Address</label>
     <textarea class="form-control rounded-3" id="staffAddress" name="address" maxlength="255" rows="2" required oninput="this.setCustomValidity('')"></textarea></div>
    <div class="mb-3"><label for="staffContactNo" class="form-label field-label">Contact Number</label>
     <input type="text" class="form-control rounded-3" id="staffContactNo" name="contactNo" maxlength="11" inputmode="numeric" pattern="[0-9]{11}" minlength="11" required aria-describedby="staffContactHelp" oninput="this.setCustomValidity('')">
     <div id="staffContactHelp" class="form-text">Enter exactly 11 digits.</div></div>
    <div class="mb-3"><label for="staffType" class="form-label field-label">Role</label><select class="form-select rounded-3" id="staffType" name="staffType" required>
     <option value="Receptionist">Receptionist</option>
    </select></div>
    <div class="mb-3"><label for="staffStatus" class="form-label field-label">Employment Status</label><select class="form-select rounded-3" id="staffStatus" name="employmentStatus" required onchange="updateSeparationRequirement()">
     <option value="Active">Active</option>
     <option value="On Leave">On Leave</option>
     <option value="Inactive">Inactive</option>
    </select></div>
    <div class="mb-3"><label for="dateJoined" class="form-label field-label">Date Joined</label><input type="date" class="form-control rounded-3" id="dateJoined" name="dateJoined" required oninput="document.getElementById('dateSeparated').setCustomValidity('')"></div>
    <div class="mb-3"><label for="dateSeparated" id="dateSeparatedLabel" class="form-label field-label">Date Separated (Inactive only)</label><input type="date" class="form-control rounded-3" id="dateSeparated" name="dateSeparated" disabled oninput="this.setCustomValidity('')"></div>
    <div class="d-flex justify-content-end gap-2 mt-4"><button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark rounded-pill px-4" id="saveStaffBtn">Add Staff</button></div>
   </form>
  </div>
 </div></div>
</div>
<!-- VIEW STAFF MODAL -->
<div class="modal fade" id="viewStaffModal" tabindex="-1" aria-labelledby="viewStaffTitle" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content p-3">
  <div class="modal-header"><h5 class="modal-title" id="viewStaffTitle">Staff Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <div class="modal-body">
   <div class="row g-4">
    <div class="col-md-6">
   <p><strong>ID:</strong> <span id="viewStaffId"></span></p>
   <p><strong>First Name:</strong> <span id="viewStaffFirstName"></span></p>
   <p><strong>Last Name:</strong> <span id="viewStaffLastName"></span></p>
   <p><strong>Role:</strong> <span id="viewStaffType"></span></p>
   <p><strong>Employment Status:</strong> <span id="viewStaffStatus"></span></p>
    </div>
    <div class="col-md-6">
   <p><strong>Address:</strong> <span id="viewStaffAddress"></span></p>
   <p><strong>Contact Number:</strong> <span id="viewStaffContactNo"></span></p>
   <p><strong>Date Joined:</strong> <span id="viewStaffDateJoined"></span></p>
   <p><strong>Date Separated:</strong> <span id="viewStaffDateSeparated"></span></p>
    </div>
   </div>
  </div>
 </div></div>
</div>
