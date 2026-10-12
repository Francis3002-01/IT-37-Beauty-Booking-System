<!-- Add/Edit form matches the client table; there is no email column. -->
<div class="modal fade" id="clientModal" tabindex="-1" aria-labelledby="clientModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="clientModalTitle">Add New Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="clientForm" method="POST" enctype="multipart/form-data" action="backend%20logic/clients_backend.php" onsubmit="return validateClientForm()">
    <div class="mb-3"><label for="clientImage" class="form-label field-label">Profile Picture (Optional)</label>
     <input type="file" class="form-control" id="clientImage" name="profileImage" accept="image/jpeg,image/png,image/webp" aria-describedby="clientImageHelp">
     <div id="clientImageHelp" class="form-text">JPG, PNG or WebP, up to 2 MB.</div></div>
                    <input type="hidden" id="editClientId" name="clientID">
                    <input type="hidden" id="clientAction" name="action" value="create">
                    <div class="mb-3">
                        <label for="clientFirstName" class="form-label field-label">First Name</label>
                        <input type="text" class="form-control rounded-3" id="clientFirstName" name="firstName" maxlength="50" required oninput="this.setCustomValidity('')">
                    </div>
                    <div class="mb-3">
                        <label for="clientLastName" class="form-label field-label">Last Name</label>
                        <input type="text" class="form-control rounded-3" id="clientLastName" name="lastName" maxlength="50" required oninput="this.setCustomValidity('')">
                    </div>
                    <div class="mb-3">
                        <label for="clientContactNo" class="form-label field-label">Contact Number</label>
                        <input type="text" class="form-control rounded-3" id="clientContactNo" name="contactNo" inputmode="numeric" pattern="[0-9]{11}" minlength="11" maxlength="11" required placeholder="e.g. 09123456789" oninput="this.setCustomValidity('')">
                        <div class="form-text">Enter exactly 11 digits.</div>
                    </div>
                    <div class="mb-3">
                        <label for="clientAddress" class="form-label field-label">Address</label>
                        <textarea class="form-control rounded-3" id="clientAddress" name="address" maxlength="255" rows="2" required placeholder="e.g. Dumaguete City, Negros Oriental" oninput="this.setCustomValidity('')"></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark rounded-pill px-4" id="saveClientBtn">Add Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View client details -->
<div class="modal fade" id="viewClientModal" tabindex="-1" aria-labelledby="viewClientTitle" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content p-3">
  <div class="modal-header"><h5 class="modal-title" id="viewClientTitle">Client Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <div class="modal-body"><div class="row g-4">
   <div class="col-md-4"><div class="client-view-photo"><img id="viewClientImage" class="client-profile-image" alt="Client profile picture" hidden></div></div>
   <div class="col-md-4">
    <p><strong>ID:</strong> <span id="viewClientId"></span></p>
    <p><strong>First Name:</strong> <span id="viewClientFirstName"></span></p>
    <p><strong>Last Name:</strong> <span id="viewClientLastName"></span></p>
   </div>
   <div class="col-md-4">
    <p><strong>Address:</strong> <span id="viewClientAddress"></span></p>
    <p><strong>Contact Number:</strong> <span id="viewClientContactNo"></span></p>
   </div>
  </div></div>
 </div></div>
</div>
