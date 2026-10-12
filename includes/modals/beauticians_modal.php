<!-- ADD / EDIT BEAUTICIAN MODAL: ordinary POST form matching beauticians table -->
<div class="modal fade" id="beauticianModal" tabindex="-1" aria-labelledby="beauticianModalTitle" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered"><div class="modal-content p-3">
  <div class="modal-header border-0 pb-0"><h5 class="modal-title fw-bold" id="beauticianModalTitle">Add New Beautician</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <div class="modal-body">
   <form id="beauticianForm" method="POST" enctype="multipart/form-data" action="backend%20logic/beauticians_backend.php" onsubmit="return validateBeauticianForm()">
    <div class="mb-3"><label for="beauticianImage" class="form-label field-label">Profile Picture (Optional)</label>
     <input type="file" class="form-control" id="beauticianImage" name="profileImage" accept="image/jpeg,image/png,image/webp" aria-describedby="beauticianImageHelp">
     <div id="beauticianImageHelp" class="form-text">JPG, PNG or WebP, up to 2 MB.</div></div>
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
     <option value="Massage Therapist">Massage Therapist</option>
    </select></div>
    <div class="mb-3"><label for="beauticianStatus" class="form-label field-label">Employment Status</label><select class="form-select rounded-3" id="beauticianStatus" name="employmentStatus" required onchange="updateSeparationRequirement()">
     <option value="Active">Active</option>
     <option value="On Leave">On Leave</option>
     <option value="Inactive">Inactive</option>
    </select></div>
    <div class="mb-3"><label for="dateJoined" class="form-label field-label">Date Joined</label><input type="date" class="form-control rounded-3" id="dateJoined" name="dateJoined" required oninput="document.getElementById('dateSeparated').setCustomValidity('')"></div>
    <div class="mb-3"><label for="dateSeparated" id="dateSeparatedLabel" class="form-label field-label">Date Separated (Inactive only)</label><input type="date" class="form-control rounded-3" id="dateSeparated" name="dateSeparated" disabled oninput="this.setCustomValidity('')"></div>
    <div class="d-flex justify-content-end gap-2 mt-4"><button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark rounded-pill px-4" id="saveBeauticianBtn">Add Beautician</button></div>
   </form>
  </div>
 </div></div>
</div>
<!-- VIEW BEAUTICIAN MODAL -->
<div class="modal fade" id="viewBeauticianModal" tabindex="-1" aria-labelledby="viewBeauticianTitle" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered">
  <div class="modal-content p-4 rounded-4 shadow">
   <div class="modal-header border-0 pb-0">
    <h5 class="modal-title fw-bold" id="viewBeauticianTitle">Beautician Details (#<span id="viewBeauticianId"></span>)</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
   </div>
   <div class="modal-body">
    <div class="text-center mb-4">
     <div class="rounded-circle shadow-sm d-inline-block overflow-hidden" style="width: 90px; height: 90px; background-color: #d1d5db;">
      <img id="viewBeauticianImage" class="w-100 h-100 object-fit-cover" alt="Beautician profile picture" width="90" height="90" hidden>
     </div>
    </div>
    <div class="row g-3">
     <div class="col-6">
      <span class="text-muted small d-block">First Name</span>
      <span id="viewBeauticianFirstName" class="fw-semibold"></span>
     </div>
     <div class="col-6">
      <span class="text-muted small d-block">Last Name</span>
      <span id="viewBeauticianLastName" class="fw-semibold"></span>
     </div>
     <div class="col-12">
      <span class="text-muted small d-block">Specialization</span>
      <span id="viewBeauticianType" class="fw-semibold"></span>
     </div>
     <div class="col-6">
      <span class="text-muted small d-block">Contact Number</span>
      <span id="viewBeauticianContactNo" class="fw-semibold"></span>
     </div>
     <div class="col-6">
      <span class="text-muted small d-block">Employment Status</span>
      <span id="viewBeauticianStatus" class="fw-semibold"></span>
     </div>
     <div class="col-12">
      <span class="text-muted small d-block">Address</span>
      <span id="viewBeauticianAddress" class="fw-semibold"></span>
     </div>
     <div class="col-6">
      <span class="text-muted small d-block">Date Joined</span>
      <span id="viewBeauticianDateJoined" class="fw-semibold"></span>
     </div>
     <div class="col-6">
      <span class="text-muted small d-block">Date Separated</span>
      <span id="viewBeauticianDateSeparated" class="fw-semibold"></span>
     </div>
    </div>
   </div>
   <div class="modal-footer border-0 pt-3">
    <button type="button" class="btn btn-dark rounded-pill px-4 w-100" data-bs-dismiss="modal">Close</button>
   </div>
  </div>
 </div>
</div>
