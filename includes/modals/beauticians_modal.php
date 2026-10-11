<!-- ADD / EDIT BEAUTICIAN MODAL: ordinary POST form matching beauticians table -->
<div class="modal fade" id="beauticianModal" tabindex="-1" aria-labelledby="beauticianModalTitle" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered"><div class="modal-content p-3">
  <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold" id="beauticianModalTitle">Add New Beautician</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <div class="modal-body">
   <form id="beauticianForm" method="POST" action="backend%20logic/beauticians_backend.php" onsubmit="return validateBeauticianForm()">
    <input type="hidden" id="editBeauticianId" name="beauticianID">
    <input type="hidden" id="beauticianAction" name="action" value="create">
    <div class="mb-3"><label for="beauticianFirstName" class="form-label field-label">First Name</label>
     <input type="text" class="form-control rounded-3" id="beauticianFirstName" name="firstName" maxlength="50" required oninput="this.setCustomValidity('')"></div>
    <div class="mb-3"><label for="beauticianLastName" class="form-label field-label">Last Name</label>
     <input type="text" class="form-control rounded-3" id="beauticianLastName" name="lastName" maxlength="50" required oninput="this.setCustomValidity('')"></div>
    <div class="mb-3"><label for="beauticianAddress" class="form-label field-label">Address</label>
     <textarea class="form-control rounded-3" id="beauticianAddress" name="address" maxlength="255" rows="2" required oninput="this.setCustomValidity('')"></textarea></div>
    <div class="mb-3"><label for="beauticianContactNo" class="form-label field-label">Contact Number</label>
     <input type="text" class="form-control rounded-3" id="beauticianContactNo" name="contactNo" maxlength="11" inputmode="numeric" pattern="[0-9]{11}" minlength="11" required aria-describedby="beauticianContactHelp" oninput="this.setCustomValidity('')">
     <div id="beauticianContactHelp" class="form-text">Enter exactly 11 digits.</div></div>
    <div class="mb-3"><label for="beauticianType" class="form-label field-label">Specialization</label><select class="form-select rounded-3" id="beauticianType" name="beauticianType" required>
     <option value="Hair Stylist">Hair Stylist</option>
     <option value="Makeup Artist">Makeup Artist</option>
     <option value="Nail Artist">Nail Artist</option>
     <option value="Esthetician">Esthetician</option>
    </select></div>
    <div class="mb-3"><label for="beauticianStatus" class="form-label field-label">Employment Status</label><select class="form-select rounded-3" id="beauticianStatus" name="employmentStatus" required>
     <option value="Active">Active</option>
     <option value="On Leave">On Leave</option>
     <option value="Inactive">Inactive</option>
    </select></div>
    <div class="mb-3"><label for="dateJoined" class="form-label field-label">Date Joined</label><input type="date" class="form-control rounded-3" id="dateJoined" name="dateJoined" required oninput="document.getElementById('dateSeparated').setCustomValidity('')"></div>
    <div class="mb-3"><label for="dateSeparated" class="form-label field-label">Date Separated (Optional)</label><input type="date" class="form-control rounded-3" id="dateSeparated" name="dateSeparated" oninput="this.setCustomValidity('')"></div>
    <div class="d-flex justify-content-end gap-2 mt-4"><button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark rounded-pill px-4" id="saveBeauticianBtn">Add Beautician</button></div>
   </form>
  </div>
 </div></div>
</div>
<!-- VIEW BEAUTICIAN MODAL -->
<div class="modal fade" id="viewBeauticianModal" tabindex="-1" aria-labelledby="viewBeauticianTitle" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content p-3">
  <div class="modal-header"><h5 class="modal-title" id="viewBeauticianTitle">Beautician Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <div class="modal-body">
   <div class="row g-4">
    <div class="col-md-6">
   <p><strong>ID:</strong> <span id="viewBeauticianId"></span></p>
   <p><strong>First Name:</strong> <span id="viewBeauticianFirstName"></span></p>
   <p><strong>Last Name:</strong> <span id="viewBeauticianLastName"></span></p>
   <p><strong>Specialization:</strong> <span id="viewBeauticianType"></span></p>
   <p><strong>Employment Status:</strong> <span id="viewBeauticianStatus"></span></p>
    </div>
    <div class="col-md-6">
   <p><strong>Address:</strong> <span id="viewBeauticianAddress"></span></p>
   <p><strong>Contact Number:</strong> <span id="viewBeauticianContactNo"></span></p>
   <p><strong>Date Joined:</strong> <span id="viewBeauticianDateJoined"></span></p>
   <p><strong>Date Separated:</strong> <span id="viewBeauticianDateSeparated"></span></p>
    </div>
   </div>
  </div>
 </div></div>
</div>
