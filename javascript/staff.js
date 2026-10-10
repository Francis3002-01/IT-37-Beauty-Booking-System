/**
 * BEAUTY RESERVE - STAFF MODULE
 * ------------------------------------------------------------
 * Front-end controller with hardcoded mock data and 
 * automatic server database bridge.
 */

// ================================================================
// 1. HARDCODED MOCK DATA
// ================================================================

const defaultStaff = [
    {
        staffID: 1,
        staffName: "Elena Rostova",
        role: "Makeup Artist",
        status: "ACTIVE",
        address: "124 Grand Ave, Suite 3B",
        contactNumber: "+1 (555) 234-5678",
        dateJoined: "Jan 15, 2024",
        avatar: "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=120&auto=format&fit=crop&q=80"
    },
    {
        staffID: 2,
        staffName: "Marcus Vance",
        role: "Makeup Artist",
        status: "ACTIVE",
        address: "89 Sunset Blvd, Suite 101",
        contactNumber: "+1 (555) 901-2345",
        dateJoined: "Mar 10, 2024",
        avatar: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=120&auto=format&fit=crop&q=80"
    },
    {
        staffID: 3,
        staffName: "Clara Oswald",
        role: "Hair stylist",
        status: "ACTIVE",
        address: "450 Ocean Drive, Apt 12",
        contactNumber: "+1 (555) 345-6789",
        dateJoined: "Jun 01, 2023",
        avatar: "https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=120&auto=format&fit=crop&q=80"
    },
    {
        staffID: 4,
        staffName: "David Tennant",
        role: "Hair Stylist",
        status: "ON LEAVE",
        address: "742 Evergreen Terrace",
        contactNumber: "+1 (555) 876-5432",
        dateJoined: "Nov 20, 2023",
        avatar: "https://images.unsplash.com/photo-1517841905240-472988babdf9?w=120&auto=format&fit=crop&q=80"
    }
];

// Automatically switch to MySQL server dataset when available
let staffList = window.SERVER_STAFF_DATA || defaultStaff;


// ================================================================
// 2. MODULE STATE
// ================================================================

let currentFilter = "All";


// ================================================================
// 3. INITIALIZE PAGE
// ================================================================

document.addEventListener("DOMContentLoaded", function () {
    renderStaff();

    // Form submit event for Create/Edit
    const form = document.getElementById("staffForm");
    if (form) {
        form.addEventListener("submit", handleFormSubmit);
    }
});


// ================================================================
// 4. RENDER STAFF CARDS
// ================================================================

function renderStaff(filteredList = null) {

    const grid = document.getElementById("staffGrid");

    if (!grid) {
        return;
    }

    const list = filteredList !== null
        ? filteredList
        : staffList;

    if (list.length === 0) {
        grid.innerHTML = `
            <div class="col-12 text-center text-muted py-5">
                No staff members found.
            </div>
        `;
        return;
    }

    grid.innerHTML = list.map(member => {

        const badgeClass = member.status === 'ON LEAVE' ? 'status-badge on-leave' : 
                          (member.status === 'INACTIVE' ? 'status-badge inactive' : 'status-badge');

        return `
            <div class="col-md-6 col-lg-6 mb-4">
                <div class="staff-card">
                    
                    <!-- Header Info & Avatar -->
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-3">
                            <img src="${member.avatar || ''}" alt="${member.staffName}" class="staff-avatar" onerror="this.src='https://via.placeholder.com/48';">
                            <div>
                                <h4 class="staff-name">${member.staffName}</h4>
                                <span class="staff-role">${member.role}</span>
                            </div>
                        </div>
                        <span class="${badgeClass}">${member.status}</span>
                    </div>

                    <!-- Details Section -->
                    <div class="staff-details-list">
                        <div class="detail-row">
                            <span class="detail-label">Address</span>
                            <span class="detail-value">${member.address}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Contact Number</span>
                            <span class="detail-value">${member.contactNumber}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Date Joined</span>
                            <span class="detail-value">${member.dateJoined}</span>
                        </div>
                    </div>

                    <!-- Actions Footer -->
                    <div class="staff-card-footer">
                        <button 
                            type="button" 
                            class="btn btn-sm btn-outline-secondary px-3 rounded-pill" 
                            onclick="editStaff(${member.staffID})">
                            EDIT
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-sm btn-outline-danger px-3 rounded-pill" 
                            onclick="deleteStaff(${member.staffID})">
                            DELETE
                        </button>
                    </div>

                </div>
            </div>
        `;
    }).join("");
}


// ================================================================
// 5. ROLE FILTER
// ================================================================

function setFilter(role, btn) {

    document.querySelectorAll(".filter-pill").forEach(pill => {
        pill.classList.remove("active");
    });

    if (btn) {
        btn.classList.add("active");
    }

    currentFilter = role;

    filterStaff();
}


// ================================================================
// 6. SEARCH + FILTER
// ================================================================

function filterStaff() {

    const searchInput = document.getElementById("searchInput");

    const query = searchInput
        ? searchInput.value.toLowerCase().trim()
        : "";

    let filtered = staffList;

    // Role filter
    if (currentFilter !== "All") {
        filtered = filtered.filter(member =>
            member.role.toLowerCase() === currentFilter.toLowerCase()
        );
    }

    // Search filter
    if (query !== "") {
        filtered = filtered.filter(member => {
            const name = member.staffName.toLowerCase();
            const role = member.role.toLowerCase();
            const address = member.address.toLowerCase();

            return (
                name.includes(query) ||
                role.includes(query) ||
                address.includes(query)
            );
        });
    }

    renderStaff(filtered);
}


// ================================================================
// 7. CREATE STAFF
// ================================================================

function openCreateModal() {

    const form = document.getElementById("staffForm");
    if (form) {
        form.reset();
    }

    const modalTitle = document.getElementById("modalTitle");
    const saveButton = document.getElementById("saveStaffBtn");
    const editID = document.getElementById("editStaffId");

    if (modalTitle) modalTitle.innerText = "Add New Staff";
    if (saveButton) saveButton.innerText = "Add Staff";
    if (editID) editID.value = "";

    if (typeof bootstrap !== "undefined") {
        const modalElement = document.getElementById("staffModal");
        if (modalElement) {
            new bootstrap.Modal(modalElement).show();
        }
    }
}


// ================================================================
// 8. EDIT STAFF
// ================================================================
// ================================================================
// 8. EDIT STAFF (Fixed to safely handle file input limits)
// ================================================================

function editStaff(id) {
    const member = staffList.find(item => item.staffID === id);
    if (!member) return;

    document.getElementById("editStaffId").value = member.staffID;
    document.getElementById("staffName").value = member.staffName;
    document.getElementById("role").value = member.role;
    document.getElementById("status").value = member.status;
    document.getElementById("address").value = member.address;
    document.getElementById("contactNumber").value = member.contactNumber;

    // Clear the file input visually since browsers block pre-filling file selectors
    const avatarFileInput = document.getElementById("avatarFile");
    if (avatarFileInput) {
        avatarFileInput.value = "";
    }

    document.getElementById("modalTitle").innerText = "Edit Staff";
    document.getElementById("saveStaffBtn").innerText = "Save Changes";

    if (typeof bootstrap !== "undefined") {
        const modalElement = document.getElementById("staffModal");
        if (modalElement) {
            new bootstrap.Modal(modalElement).show();
        }
    }
}


// ================================================================
// 9. SAVE / UPDATE HANDLER (Preserves existing avatar if no new file is chosen)
// ================================================================

function handleFormSubmit(e) {
    e.preventDefault();

    const editID = document.getElementById("editStaffId").value;
    const name = document.getElementById("staffName").value;
    const role = document.getElementById("role").value;
    const status = document.getElementById("status").value;
    const address = document.getElementById("address").value;
    const contact = document.getElementById("contactNumber").value;
    
    const avatarFileInput = document.getElementById("avatarFile");
    let newAvatarUrl = "";

    // Check if the user selected a new profile picture file
    if (avatarFileInput && avatarFileInput.files && avatarFileInput.files[0]) {
        newAvatarUrl = URL.createObjectURL(avatarFileInput.files[0]);
    }

    if (editID) {
        // Edit existing staff member
        const member = staffList.find(item => item.staffID === parseInt(editID));
        if (member) {
            member.staffName = name;
            member.role = role;
            member.status = status;
            
            // Only update avatar if a new image was uploaded; otherwise, keep the old one!
            if (newAvatarUrl) {
                member.avatar = newAvatarUrl;
            }

            member.address = address;
            member.contactNumber = contact;
        }
    } else {
        // Create new staff member
        const newID = staffList.length > 0 ? Math.max(...staffList.map(s => s.staffID)) + 1 : 1;
        staffList.push({
            staffID: newID,
            staffName: name,
            role: role,
            status: status,
            avatar: newAvatarUrl, // Will use the new file preview or stay blank
            address: address,
            contactNumber: contact,
            dateJoined: new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
        });
    }

    // Hide modal
    const modalElement = document.getElementById("staffModal");
    const modalInstance = bootstrap.Modal.getInstance(modalElement);
    if (modalInstance) {
        modalInstance.hide();
    }

    // Reset file input
    if (avatarFileInput) {
        avatarFileInput.value = "";
    }

    filterStaff();
}


// ================================================================
// 9. SAVE / UPDATE HANDLER (Updated to handle local image files)
// ================================================================

function handleFormSubmit(e) {
    e.preventDefault();

    const editID = document.getElementById("editStaffId").value;
    const name = document.getElementById("staffName").value;
    const role = document.getElementById("role").value;
    const status = document.getElementById("status").value;
    const address = document.getElementById("address").value;
    const contact = document.getElementById("contactNumber").value;
    
    const avatarFileInput = document.getElementById("avatarFile");
    let avatarUrl = "";

    // If a local file was selected, create a temporary preview URL for frontend testing
    if (avatarFileInput && avatarFileInput.files && avatarFileInput.files[0]) {
        avatarUrl = URL.createObjectURL(avatarFileInput.files[0]);
    }

    if (editID) {
        // Edit existing
        const member = staffList.find(item => item.staffID === parseInt(editID));
        if (member) {
            member.staffName = name;
            member.role = role;
            member.status = status;
            if (avatarUrl) {
                member.avatar = avatarUrl; // Update avatar only if a new file was chosen
            }
            member.address = address;
            member.contactNumber = contact;
        }
    } else {
        // Create new
        const newID = staffList.length > 0 ? Math.max(...staffList.map(s => s.staffID)) + 1 : 1;
        staffList.push({
            staffID: newID,
            staffName: name,
            role: role,
            status: status,
            avatar: avatarUrl, // Will use the local file URL or remain blank
            address: address,
            contactNumber: contact,
            dateJoined: new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
        });
    }

    // Hide modal
    const modalElement = document.getElementById("staffModal");
    const modalInstance = bootstrap.Modal.getInstance(modalElement);
    if (modalInstance) {
        modalInstance.hide();
    }

    // Reset file input value
    if (avatarFileInput) {
        avatarFileInput.value = "";
    }

    filterStaff();
}


// ================================================================
// 10. DELETE STAFF
// ================================================================

function deleteStaff(id) {

    const member = staffList.find(item => item.staffID === id);

    if (!member) return;

    const confirmed = confirm(`Are you sure you want to delete "${member.staffName}"?`);

    if (!confirmed) return;

    const index = staffList.findIndex(item => item.staffID === id);

    if (index !== -1) {
        staffList.splice(index, 1);
    }

    filterStaff();
}