/**
 * BEAUTY RESERVE - SERVICES MODULE
 * ------------------------------------------------------------
 * Front-end preview using hardcoded mock data.
 * No database/API connection yet.
 */

// ================================================================
// 1. HARDCODED MOCK DATA
// ================================================================

const services = [
    {
        serviceID: 1,
        serviceName: "Deep Cleansing Facial",
        category: "Face",
        duration: 60,
        price: 2000.00,
        description: "Rejuvenating facial treatment that cleanses pores, exfoliates dead skin cells, and hydrates your skin.",
        image: ""
    },
    {
        serviceID: 2,
        serviceName: "Hair Rebond & Treatment",
        category: "Hair",
        duration: 120,
        price: 3500.00,
        description: "Premium hair straightening treatment combined with deep conditioning for smooth, shiny hair.",
        image: ""
    },
    {
        serviceID: 3,
        serviceName: "Gel Manicure & Pedicure",
        category: "Nails",
        duration: 45,
        price: 1200.00,
        description: "Long-lasting gel polish application with full nail cleaning and relaxing hand/foot massage.",
        image: ""
    },
    {
        serviceID: 4,
        serviceName: "Aromatherapy Full Body Massage",
        category: "Massage",
        duration: 90,
        price: 2500.00,
        description: "Soothing body massage with natural essential oils designed to reduce stress and physical tension.",
        image: ""
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
    renderServices();

    // Form submit event for Create/Edit
    const form = document.getElementById("serviceForm");
    if (form) {
        form.addEventListener("submit", handleFormSubmit);
    }
});


// ================================================================
// 4. RENDER SERVICE CARDS
// ================================================================

function renderServices(filteredList = null) {

    const grid = document.getElementById("servicesGrid");

    if (!grid) {
        return;
    }

    const list = filteredList !== null
        ? filteredList
        : services;

    if (list.length === 0) {
        grid.innerHTML = `
            <div class="col-12 text-center text-muted py-5">
                No services found.
            </div>
        `;
        return;
    }

    grid.innerHTML = list.map(service => {

        const formattedPrice = `PHP ${parseFloat(service.price).toFixed(2)}`;

        return `
            <div class="col-md-6 col-lg-6 mb-4">
                <div class="service-card">
                    
                    <!-- Top Image Banner Placeholder -->
                    <div class="service-image-placeholder">
                        ${service.image ? `<img src="${service.image}" alt="${service.serviceName}">` : ``}
                    </div>

                    <!-- Title and Category / Duration Meta -->
                    <h4 class="service-title">${service.serviceName}</h4>
                    
                    <div class="d-flex justify-content-between service-meta">
                        <span>${service.category}</span>
                        <span>${service.duration} min.</span>
                    </div>

                    <!-- Description -->
                    <p class="service-description">
                        ${service.description}
                    </p>

                    <!-- Tag & Price -->
                    <div class="service-price-row">
                        <span class="service-type-tag"></span>
                        <div class="service-price">${formattedPrice}</div>
                    </div>

                    <!-- Card Actions -->
                    <div class="service-card-footer">
                        <button 
                            type="button" 
                            class="btn btn-sm btn-outline-dark px-3 rounded-pill" 
                            onclick="viewService(${service.serviceID})">
                            VIEW
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-sm btn-outline-secondary px-3 rounded-pill" 
                            onclick="editService(${service.serviceID})">
                            EDIT
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-sm btn-outline-danger px-3 rounded-pill" 
                            onclick="deleteService(${service.serviceID})">
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

    filterServices();
}


// ================================================================
// 6. SEARCH + FILTER
// ================================================================

function filterServices() {

    const searchInput = document.getElementById("searchInput");

    const query = searchInput
        ? searchInput.value.toLowerCase().trim()
        : "";

    let filtered = services;

    // Category filter
    if (currentFilter !== "All") {
        filtered = filtered.filter(service =>
            service.category.toLowerCase() === currentFilter.toLowerCase()
        );
    }

    // Search filter
    if (query !== "") {
        filtered = filtered.filter(service => {
            const name = service.serviceName.toLowerCase();
            const category = service.category.toLowerCase();
            const desc = service.description.toLowerCase();

            return (
                name.includes(query) ||
                category.includes(query) ||
                desc.includes(query)
            );
        });
    }

    renderServices(filtered);
}


// ================================================================
// 7. CREATE SERVICE
// ================================================================

function openCreateModal() {

    const form = document.getElementById("serviceForm");
    if (form) {
        form.reset();
    }

    const modalTitle = document.getElementById("modalTitle");
    const saveButton = document.getElementById("saveServiceBtn");
    const editID = document.getElementById("editServiceId");

    if (modalTitle) modalTitle.innerText = "Add New Service";
    if (saveButton) saveButton.innerText = "Add Service";
    if (editID) editID.value = "";

    if (typeof bootstrap !== "undefined") {
        const modalElement = document.getElementById("serviceModal");
        if (modalElement) {
            new bootstrap.Modal(modalElement).show();
        }
    }
}


// ================================================================
// 8. VIEW SERVICE
// ================================================================

function viewService(id) {

    const service = services.find(item => item.serviceID === id);

    if (!service) return;

    const title = document.getElementById("viewModalTitle");
    const body = document.getElementById("viewModalBody");

    if (!title || !body) return;

    title.innerText = "Service Details";

    body.innerHTML = `
        <p><strong>ID:</strong> #${service.serviceID}</p>
        <p><strong>Service Name:</strong> ${service.serviceName}</p>
        <p><strong>Category:</strong> ${service.category}</p>
        <p><strong>Duration:</strong> ${service.duration} minutes</p>
        <p><strong>Price:</strong> PHP ${parseFloat(service.price).toFixed(2)}</p>
        <p><strong>Description:</strong> ${service.description}</p>
    `;

    if (typeof bootstrap !== "undefined") {
        const modalElement = document.getElementById("viewModal");
        if (modalElement) {
            new bootstrap.Modal(modalElement).show();
        }
    }
}


// ================================================================
// 9. EDIT SERVICE
// ================================================================

function editService(id) {

    const service = services.find(item => item.serviceID === id);

    if (!service) return;

    document.getElementById("editServiceId").value = service.serviceID;
    document.getElementById("serviceName").value = service.serviceName;
    document.getElementById("category").value = service.category;
    document.getElementById("duration").value = service.duration;
    document.getElementById("price").value = service.price;
    document.getElementById("description").value = service.description;

    document.getElementById("modalTitle").innerText = "Edit Service";
    document.getElementById("saveServiceBtn").innerText = "Save Changes";

    if (typeof bootstrap !== "undefined") {
        const modalElement = document.getElementById("serviceModal");
        if (modalElement) {
            new bootstrap.Modal(modalElement).show();
        }
    }
}


// ================================================================
// 10. SAVE / UPDATE HANDLER
// ================================================================

function handleFormSubmit(e) {
    e.preventDefault();

    const editID = document.getElementById("editServiceId").value;
    const name = document.getElementById("serviceName").value;
    const category = document.getElementById("category").value;
    const duration = parseInt(document.getElementById("duration").value);
    const price = parseFloat(document.getElementById("price").value);
    const description = document.getElementById("description").value;

    if (editID) {
        // Edit existing
        const service = services.find(item => item.serviceID === parseInt(editID));
        if (service) {
            service.serviceName = name;
            service.category = category;
            service.duration = duration;
            service.price = price;
            service.description = description;
        }
    } else {
        // Create new
        const newID = services.length > 0 ? Math.max(...services.map(s => s.serviceID)) + 1 : 1;
        services.push({
            serviceID: newID,
            serviceName: name,
            category: category,
            duration: duration,
            price: price,
            description: description,
            image: ""
        });
    }

    // Hide modal
    const modalElement = document.getElementById("serviceModal");
    const modalInstance = bootstrap.Modal.getInstance(modalElement);
    if (modalInstance) {
        modalInstance.hide();
    }

    filterServices();
}


// ================================================================
// 11. DELETE SERVICE
// ================================================================

function deleteService(id) {

    const service = services.find(item => item.serviceID === id);

    if (!service) return;

    const confirmed = confirm(`Are you sure you want to delete "${service.serviceName}"?`);

    if (!confirmed) return;

    const index = services.findIndex(item => item.serviceID === id);

    if (index !== -1) {
        services.splice(index, 1);
    }

    filterServices();
}