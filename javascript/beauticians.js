/**
 * BEAUTY RESERVE - BEAUTICIANS MODULE
 * ------------------------------------------------------------
 * Front-end preview using hardcoded mock data.
 * No database/API connection yet.
 */

// ================================================================
// 1. HARDCODED MOCK DATA
// ================================================================

const beauticians = [
    {
        resourceID: 1,
        firstName: "Maria",
        lastName: "Santos",
        resourceType: "Hair Stylist"
    },
    {
        resourceID: 2,
        firstName: "Carlo",
        lastName: "Reyes",
        resourceType: "Makeup Artist"
    },
    {
        resourceID: 3,
        firstName: "Angela",
        lastName: "Cruz",
        resourceType: "Hair Stylist"
    },
    {
        resourceID: 4,
        firstName: "Sofia",
        lastName: "Garcia",
        resourceType: "Nail Specialist"
    },
    {
        resourceID: 5,
        firstName: "Daniel",
        lastName: "Dela Cruz",
        resourceType: "Makeup Artist"
    },
    {
        resourceID: 6,
        firstName: "Isabella",
        lastName: "Reyes",
        resourceType: "Nail Specialist"
    }
];


// ================================================================
// 2. MODULE STATE
// ================================================================

let currentFilter = "All";


// ================================================================
// 3. INITIALIZE PAGE
// ================================================================

document.addEventListener("DOMContentLoaded", function () {
    renderBeauticians();
});


// ================================================================
// 4. RENDER BEAUTICIAN CARDS
// ================================================================

function renderBeauticians(filteredList = null) {

    const grid = document.getElementById("beauticiansGrid");

    if (!grid) {
        return;
    }

    const list = filteredList !== null
        ? filteredList
        : beauticians;

    if (list.length === 0) {
        grid.innerHTML = `
            <div class="col-12 text-center text-muted py-5">
                No beauticians found.
            </div>
        `;

        return;
    }

    grid.innerHTML = list.map(beautician => {

        return `
            <div class="col-md-6 col-lg-4 mb-4">

                <div class="beautician-card">

                    <div class="d-flex justify-content-between align-items-start mb-3">

                        <div>
                            <h5 class="fw-bold mb-1">
                                ${beautician.firstName}
                                ${beautician.lastName}
                            </h5>

                            <div class="text-muted">
                                ${beautician.resourceType}
                            </div>
                        </div>

                        <span class="badge bg-light text-dark">
                            #${beautician.resourceID}
                        </span>

                    </div>

                    <div class="mb-3">

                        <div class="field-label">
                            Resource Type
                        </div>

                        <div class="field-value">
                            ${beautician.resourceType}
                        </div>

                    </div>

                    <div class="border-top pt-3 d-flex justify-content-end gap-2">

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-dark px-3 rounded-pill"
                            onclick="viewBeautician(${beautician.resourceID})">
                            VIEW
                        </button>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary px-3 rounded-pill"
                            onclick="editBeautician(${beautician.resourceID})">
                            EDIT
                        </button>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger px-3 rounded-pill"
                            onclick="deleteBeautician(${beautician.resourceID})">
                            DELETE
                        </button>

                    </div>

                </div>

            </div>
        `;

    }).join("");
}


// ================================================================
// 5. CATEGORY FILTER
// ================================================================

function setFilter(type, btn) {

    document.querySelectorAll(".filter-pill").forEach(pill => {
        pill.classList.remove("active");
    });

    if (btn) {
        btn.classList.add("active");
    }

    currentFilter = type;

    filterBeauticians();
}


// ================================================================
// 6. SEARCH + FILTER
// ================================================================

function filterBeauticians() {

    const searchInput = document.getElementById("searchInput");

    const query = searchInput
        ? searchInput.value.toLowerCase().trim()
        : "";

    let filtered = beauticians;

    // ------------------------------------------------------------
    // Category filter
    // ------------------------------------------------------------

    if (currentFilter !== "All") {

        filtered = filtered.filter(beautician =>
            beautician.resourceType.toLowerCase() ===
            currentFilter.toLowerCase()
        );

    }

    // ------------------------------------------------------------
    // Search filter
    // ------------------------------------------------------------

    if (query !== "") {

        filtered = filtered.filter(beautician => {

            const fullName =
                `${beautician.firstName} ${beautician.lastName}`
                .toLowerCase();

            const resourceType =
                beautician.resourceType.toLowerCase();

            return (
                fullName.includes(query) ||
                resourceType.includes(query)
            );

        });

    }

    renderBeauticians(filtered);
}


// ================================================================
// 7. CREATE BEAUTICIAN
// ================================================================
function openCreateModal() {
    const form = document.getElementById("beauticianForm");
    if (form) form.reset();

    const title = document.getElementById("modalTitle");
    if (title) title.innerText = "Add New Beauticians";

    const saveBtn = document.getElementById("saveBeauticianBtn");
    if (saveBtn) saveBtn.innerText = "Save Beautician";

    const editId = document.getElementById("editBeauticianId");
    if (editId) editId.value = "";

    const modalEl = document.getElementById("beauticianModal");
    if (modalEl) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
    }
}

// ================================================================
// 8. VIEW BEAUTICIAN
// ================================================================

function viewBeautician(id) {

    const beautician = beauticians.find(
        item => item.resourceID === id
    );

    if (!beautician) {
        return;
    }

    const title =
        document.getElementById("viewModalTitle");

    const body =
        document.getElementById("viewModalBody");

    if (!title || !body) {
        return;
    }

    title.innerText =
        `Beautician Details`;

    body.innerHTML = `
        <p>
            <strong>ID:</strong>
            ${beautician.resourceID}
        </p>

        <p>
            <strong>Name:</strong>
            ${beautician.firstName}
            ${beautician.lastName}
        </p>

        <p>
            <strong>Resource Type:</strong>
            ${beautician.resourceType}
        </p>
    `;

    if (typeof bootstrap !== "undefined") {

        const modalElement =
            document.getElementById("viewModal");

        if (modalElement) {
            new bootstrap.Modal(modalElement).show();
        }

    }
}


// ================================================================
// 9. EDIT BEAUTICIAN
// ================================================================

function editBeautician(id) {

    const beautician = beauticians.find(
        item => item.resourceID === id
    );

    if (!beautician) {
        return;
    }

    const editID =
        document.getElementById("editBeauticianId");

    const firstName =
        document.getElementById("firstName");

    const lastName =
        document.getElementById("lastName");

    const resourceType =
        document.getElementById("resourceType");

    const modalTitle =
        document.getElementById("modalTitle");

    const saveButton =
        document.getElementById("saveBeauticianBtn");

    if (editID) {
        editID.value = beautician.resourceID;
    }

    if (firstName) {
        firstName.value = beautician.firstName;
    }

    if (lastName) {
        lastName.value = beautician.lastName;
    }

    if (resourceType) {
        resourceType.value = beautician.resourceType;
    }

    if (modalTitle) {
        modalTitle.innerText =
            `Edit Beautician`;
    }

    if (saveButton) {
        saveButton.innerText =
            "Save Changes";
    }

    if (typeof bootstrap !== "undefined") {

        const modalElement =
            document.getElementById("beauticianModal");

        if (modalElement) {
            new bootstrap.Modal(modalElement).show();
        }

    }
}


// ================================================================
// 10. DELETE BEAUTICIAN
// ================================================================

function deleteBeautician(id) {

    const beautician = beauticians.find(
        item => item.resourceID === id
    );

    if (!beautician) {
        return;
    }

    const confirmed = confirm(
        `Delete ${beautician.firstName} ${beautician.lastName}?`
    );

    if (!confirmed) {
        return;
    }

    const index = beauticians.findIndex(
        item => item.resourceID === id
    );

    if (index !== -1) {
        beauticians.splice(index, 1);
    }

    filterBeauticians();
}