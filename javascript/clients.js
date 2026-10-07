/**
 * CLIENTS MODULE JAVASCRIPT LOGIC
 * ------------------------------------------------------------------
 * Purpose: Manages client listings, searches, filter sorting, modal forms,
 * and CRUD actions using mock data ready for MySQL database connection.
 */

// ------------------------------------------------------------------
// 1. HARDCODED IN-MEMORY DATABASE (MOCK DATA)
// ------------------------------------------------------------------
const clientDb = {
    clients: [
        { clientID: 1, lastName: 'Pham', firstName: 'Hanni', address: '123 Hibiscus St., Dumaguete City', contactNo: '09171234567', dateCreated: '2026-01-10' },
        { clientID: 2, lastName: 'Smith', firstName: 'Jane', address: '456 Real St., Dumaguete City', contactNo: '09987654321', dateCreated: '2026-03-15' },
        { clientID: 3, lastName: 'Johnson', firstName: 'Michael', address: 'Poblacion, Valencia', contactNo: '09112233445', dateCreated: '2026-05-20' },
        { clientID: 4, lastName: 'Santos', firstName: 'Maria', address: 'Main Highway, Dauin', contactNo: '09223344556', dateCreated: '2026-07-08' },
        { clientID: 5, lastName: 'Reyes', firstName: 'Carlo', address: 'Bagacay, Dumaguete City', contactNo: '09334455667', dateCreated: '2026-09-01' }
    ]
};

let currentFilter = 'All';
let deleteClientTargetId = null;

document.addEventListener('DOMContentLoaded', () => {
    renderClients();
});

// ------------------------------------------------------------------
// 2. RENDERING LOGIC (MATCHING WIREFRAME LAYOUT)
// ------------------------------------------------------------------
function renderClients(filteredList = null) {
    const listContainer = document.getElementById('clientsList');
    if (!listContainer) return;

    const list = filteredList || clientDb.clients;

    if (list.length === 0) {
        listContainer.innerHTML = `<div class="text-center text-muted py-5">No clients found.</div>`;
        return;
    }

    listContainer.innerHTML = list.map(client => {
        return `
            <div class="client-row-card">
                <!-- Circular Avatar Placeholder -->
                <div class="client-avatar-placeholder"></div>

                <!-- Info Wrapper -->
                <div class="client-info-wrapper">
                    <!-- Top Row: Name and Edit/Delete Buttons -->
                    <div class="client-card-header">
                        <h3 class="client-name">${client.firstName} ${client.lastName}</h3>
                        <div class="client-actions">
                            <button class="btn-card-action" onclick="editClient(${client.clientID})">Edit</button>
                            <button class="btn-card-action btn-delete" onclick="confirmDeleteClient(${client.clientID})">Delete</button>
                        </div>
                    </div>

                    <!-- Bottom Details: Address and Contact Number -->
                    <div class="client-details-grid">
                        <div class="client-detail-item">
                            <span class="client-detail-label">Address</span>
                            <span class="client-detail-value">${client.address}</span>
                        </div>
                        <div class="client-detail-item">
                            <span class="client-detail-label">Contact Number</span>
                            <span class="client-detail-value">${client.contactNo}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// ------------------------------------------------------------------
// 3. FILTERING AND SEARCH LOGIC
// ------------------------------------------------------------------
function setFilter(filterType, btn) {
    document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    currentFilter = filterType;
    filterClients();
}

function filterClients() {
    const query = document.getElementById('searchInput').value.toLowerCase().trim();
    let result = [...clientDb.clients];

    // Apply Sorting/Filtering Options
    if (currentFilter === 'Recent') {
        result.sort((a, b) => new Date(b.dateCreated) - new Date(a.dateCreated));
    } else if (currentFilter === 'AZ') {
        // Sort by full displayed name (First Name Last Name)
        result.sort((a, b) => {
            const nameA = `${a.firstName} ${a.lastName}`;
            const nameB = `${b.firstName} ${b.lastName}`;
            return nameA.localeCompare(nameB);
        });
    }

    // Apply Search Query
    if (query !== '') {
        result = result.filter(c => 
            c.firstName.toLowerCase().includes(query) ||
            c.lastName.toLowerCase().includes(query) ||
            c.address.toLowerCase().includes(query) ||
            c.contactNo.includes(query)
        );
    }

    renderClients(result);
}

// ------------------------------------------------------------------
// 4. CRUD & MODAL ACTIONS (DATABASE READY)
// ------------------------------------------------------------------
function openCreateModal() {
    const title = document.getElementById('clientModalTitle');
    const form = document.getElementById('clientForm');
    if (title) title.innerText = 'Add New Client';
    if (form) form.reset();
    document.getElementById('editClientId').value = '';
}

function editClient(id) {
    const client = clientDb.clients.find(c => c.clientID === id);
    if (!client) return;

    document.getElementById('clientModalTitle').innerText = `Edit Client (${client.firstName} ${client.lastName})`;
    document.getElementById('editClientId').value = client.clientID;
    document.getElementById('clientFirstName').value = client.firstName;
    document.getElementById('clientLastName').value = client.lastName;
    document.getElementById('clientAddress').value = client.address;
    document.getElementById('clientContactNo').value = client.contactNo;

    const modalEl = document.getElementById('clientModal');
    if (modalEl) new bootstrap.Modal(modalEl).show();
}

function handleClientFormSubmit(event) {
    event.preventDefault();

    const editId = document.getElementById('editClientId').value;
    const firstName = document.getElementById('clientFirstName').value;
    const lastName = document.getElementById('clientLastName').value;
    const address = document.getElementById('clientAddress').value;
    const contactNo = document.getElementById('clientContactNo').value;

    if (editId) {
        // Edit Existing Client
        const client = clientDb.clients.find(c => c.clientID == editId);
        if (client) {
            client.firstName = firstName;
            client.lastName = lastName;
            client.address = address;
            client.contactNo = contactNo;
        }
    } else {
        // Create New Client
        const newClient = {
            clientID: clientDb.clients.length + 1,
            firstName,
            lastName,
            address,
            contactNo,
            dateCreated: new Date().toISOString().split('T')[0]
        };
        clientDb.clients.push(newClient);
    }

    const modalEl = document.getElementById('clientModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();

    filterClients();
}

function confirmDeleteClient(id) {
    deleteClientTargetId = id;
    const textEl = document.getElementById('deleteClientIdText');
    if (textEl) textEl.innerText = `#${id}`;
    
    const modalEl = document.getElementById('deleteClientConfirmModal');
    if (modalEl) new bootstrap.Modal(modalEl).show();
}

function executeDeleteClient() {
    if (deleteClientTargetId) {
        clientDb.clients = clientDb.clients.filter(c => c.clientID !== deleteClientTargetId);
        deleteClientTargetId = null;

        const modalEl = document.getElementById('deleteClientConfirmModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        filterClients();
    }
}