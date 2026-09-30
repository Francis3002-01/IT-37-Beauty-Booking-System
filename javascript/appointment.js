/**
 * APPOINTMENTS MODULE JAVASCRIPT LOGIC (MOCK DATA / FRONT-END ONLY)
 * ------------------------------------------------------------------
 * Purpose: Manages UI interactivity, filtering, modal forms, conflict checking,
 * and CRUD actions using an in-memory array before connecting to MySQL/PHP.
 */

// ------------------------------------------------------------------
// 1. HARDCODED IN-MEMORY DATABASE (MOCK DATA)
// ------------------------------------------------------------------

// Database State
const db = {
    users: [
        { userID: 1, userRole: 'Administrator', userName: 'admin_john', lastName: 'Orevillo', firstName: 'John Francis', address: 'Dauin', contactNo: '09123456789' }
    ],
    clients: [
        { clientID: 1, lastName: 'Smith', firstName: 'Jane', address: 'Dumaguete City', contactNo: '09987654321' },
        { clientID: 2, lastName: 'Johnson', firstName: 'Michael', address: 'Valencia', contactNo: '09112233445' }
    ],
    resources: [
        { resourceID: 1, resourceType: 'Hair Stylist', lastName: 'Santos', firstName: 'Maria', address: 'Dauin', contactNo: '09223344556', employmentStatus: 'Active', dateJoined: '2025-01-10', dateSeparated: null },
        { resourceID: 2, resourceType: 'Makeup Artist', lastName: 'Reyes', firstName: 'Carlo', address: 'Dumaguete', contactNo: '09334455667', employmentStatus: 'Active', dateJoined: '2025-03-15', dateSeparated: null }
    ],
    services: [
        { serviceID: 1, serviceName: 'Signature Haircut & Styling', serviceType: 'Hair', description: 'Complete wash, cut, and professional blowout styling.', basePrice: 750.00, duration: 60 },
        { serviceID: 2, serviceName: 'Bridal Glam Makeup', serviceType: 'Makeup', description: 'Full glam makeup application with lashes and touch-up kit.', basePrice: 1500.00, duration: 90 }
    ],
    availability: [
        { availabilityID: 1, resourceID: 1, availableDate: '2026-10-05', startTime: '08:00', endTime: '17:00' },
        { availabilityID: 2, resourceID: 2, availableDate: '2026-10-05', startTime: '09:00', endTime: '18:00' },
        { availabilityID: 3, resourceID: 1, availableDate: '2026-10-06', startTime: '08:00', endTime: '17:00' },
        { availabilityID: 4, resourceID: 2, availableDate: '2026-10-06', startTime: '09:00', endTime: '18:00' }
    ],
    appointments: [
        {
            appointmentID: 'APT-1001',
            userID: 1,
            clientID: 1,
            appointmentDate: '2026-10-05',
            startTime: '09:00',
            endTime: '10:00',
            appointmentType: 'Salon service',
            venue: 'Salon Address (Main Branch)',
            appointmentStatus: 'Confirmed',
            totalCost: 750.00,
            paymentStatus: 'Paid',
            appointmentNotes: 'Prefers gentle hair products.',
            serviceID: 1,
            resourceID: 1
        },
        {
            appointmentID: 'APT-1002',
            userID: 1,
            clientID: 2,
            appointmentDate: '2026-10-05',
            startTime: '13:00',
            endTime: '14:30',
            appointmentType: 'Home service',
            venue: '123 Acacia St., Dumaguete',
            appointmentStatus: 'Pending',
            totalCost: 1500.00,
            paymentStatus: 'Unpaid',
            appointmentNotes: 'Bridal trial makeup session.',
            serviceID: 2,
            resourceID: 2
        }
    ],
    payments: [
        { paymentID: 1, clientID: 1, appointmentID: 'APT-1001', amount: 750.00, paymentMethod: 'Cash', paymentDate: '2026-10-05' }
    ]
};

let currentFilter = 'All';
let deleteTargetId = null;

document.addEventListener('DOMContentLoaded', () => {
    renderAppointments();
    populateDropdowns();
    document.getElementById('appointmentDate').value = '2026-10-05';
});

function populateDropdowns() {
    const serviceSelect = document.getElementById('serviceSelect');
    serviceSelect.innerHTML = db.services.map(s => `<option value="${s.serviceID}">${s.serviceName} - ₱${s.basePrice.toFixed(2)}</option>`).join('');

    const resourceSelect = document.getElementById('resourceSelect');
    resourceSelect.innerHTML = db.resources.map(r => `<option value="${r.resourceID}">${r.firstName} ${r.lastName} (${r.resourceType})</option>`).join('');
}

function renderAppointments(filteredList = null) {
    const grid = document.getElementById('appointmentsGrid');
    const list = filteredList || db.appointments;

    if (list.length === 0) {
        grid.innerHTML = `<div class="col-12 text-center text-muted py-5">No appointments found.</div>`;
        return;
    }

    grid.innerHTML = list.map(apt => {
        const client = db.clients.find(c => c.clientID === apt.clientID) || { firstName: 'Unknown', lastName: 'Client' };
        const service = db.services.find(s => s.serviceID === apt.serviceID) || { serviceName: 'Service', serviceType: 'General' };
        const resource = db.resources.find(r => r.resourceID === apt.resourceID) || { firstName: 'Staff', lastName: '' };
        const paymentBadgeClass = apt.paymentStatus === 'Paid' ? 'badge-paid' : 'badge-unpaid';

        return `
            <div class="col-md-6">
                <div class="appointment-item-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">${apt.appointmentID}</h5>
                            <div class="text-muted" style="font-size: 0.85rem;">${apt.appointmentDate} | ${apt.startTime} - ${apt.endTime}</div>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="${paymentBadgeClass}">${apt.paymentStatus}</span>
                            <span class="badge-status">${apt.appointmentStatus}</span>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="field-label">Appointment Type</div>
                            <div class="field-value">${apt.appointmentType}</div>
                        </div>
                        <div class="col-6">
                            <div class="field-label">Service Type</div>
                            <div class="field-value">${service.serviceType} (${service.serviceName})</div>
                        </div>
                        <div class="col-12">
                            <div class="field-label">Venue</div>
                            <div class="field-value">${apt.venue}</div>
                        </div>
                        <div class="col-12">
                            <div class="field-label">Client(s)</div>
                            <div class="field-value">${client.firstName} ${client.lastName}</div>
                        </div>
                        <div class="col-12">
                            <div class="field-label">Assigned Beautician(s)</div>
                            <div class="field-value">${resource.firstName} ${resource.lastName} (${resource.resourceType})</div>
                        </div>
                        <div class="col-12">
                            <div class="field-label">Appointment Notes</div>
                            <div class="field-value text-muted">${apt.appointmentNotes || 'None'}</div>
                        </div>
                    </div>

                    <div class="border-top pt-3 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="field-label">Service Cost</div>
                            <div class="fw-bold fs-6">₱${Number(apt.totalCost).toFixed(2)}</div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-dark px-3 rounded-pill" onclick="viewAppointment('${apt.appointmentID}')">VIEW</button>
                            <button class="btn btn-sm btn-outline-secondary px-3 rounded-pill" onclick="editAppointment('${apt.appointmentID}')">EDIT</button>
                            <button class="btn btn-sm btn-outline-danger px-3 rounded-pill" onclick="confirmDeleteAppointment('${apt.appointmentID}')">DELETE</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function setFilter(status, btn) {
    document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    currentFilter = status;
    filterAppointments();
}

function filterAppointments() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    let filtered = db.appointments;

    if (currentFilter !== 'All') {
        filtered = filtered.filter(a => a.appointmentStatus === currentFilter);
    }

    if (query.trim() !== '') {
        filtered = filtered.filter(a => {
            const client = db.clients.find(c => c.clientID === a.clientID);
            const clientName = client ? `${client.firstName} ${client.lastName}`.toLowerCase() : '';
            return a.appointmentID.toLowerCase().includes(query) ||
                   clientName.includes(query) ||
                   a.venue.toLowerCase().includes(query) ||
                   a.appointmentType.toLowerCase().includes(query);
        });
    }

    renderAppointments(filtered);
}

function toggleVenueInput() {
    const type = document.getElementById('appointmentType').value;
    const venueWrapper = document.getElementById('venueWrapper');
    const venueInput = document.getElementById('venueInput');
    if (type === 'Home service') {
        venueWrapper.style.display = 'block';
        venueInput.required = true;
    } else {
        venueWrapper.style.display = 'none';
        venueInput.required = false;
        venueInput.value = '';
    }
}

function updateServiceCost() {}

function validateAvailability() {
    const resourceID = parseInt(document.getElementById('resourceSelect').value);
    const date = document.getElementById('appointmentDate').value;
    const startTime = document.getElementById('startTime').value;
    const endTime = document.getElementById('endTime').value;
    const editId = document.getElementById('editAppointmentId').value;
    const banner = document.getElementById('conflictBanner');

    if (!date || !startTime || !endTime) {
        banner.style.backgroundColor = '#f8f9fa';
        banner.style.color = '#495057';
        banner.innerText = 'Conflict Check: Select date and time';
        return true;
    }

    const avail = db.availability.find(a => a.resourceID === resourceID && a.availableDate === date);
    if (!avail || startTime < avail.startTime || endTime > avail.endTime) {
        banner.style.backgroundColor = '#fff5f5';
        banner.style.color = '#fa5252';
        banner.innerText = 'Conflict Check: Resource unavailable on schedule';
        return false;
    }

    const overlap = db.appointments.find(a => {
        if (editId && a.appointmentID === editId) return false;
        if (a.resourceID === resourceID && a.appointmentDate === date && a.appointmentStatus !== 'Cancelled') {
            return (startTime < a.endTime && endTime > a.startTime);
        }
        return false;
    });

    if (overlap) {
        banner.style.backgroundColor = '#fff5f5';
        banner.style.color = '#fa5252';
        banner.innerText = `Conflict Check: Overlapping booking (${overlap.startTime} - ${overlap.endTime})`;
        return false;
    }

    banner.style.backgroundColor = '#e6fcf5';
    banner.style.color = '#0ca678';
    banner.innerText = 'Conflict Check: No Overlaps: Clear';
    return true;
}

function openCreateModal() {
    document.getElementById('modalTitle').innerText = 'Create New Appointment';
    document.getElementById('saveAppointmentBtn').innerText = 'Create Appointment';
    document.getElementById('appointmentForm').reset();
    document.getElementById('editAppointmentId').value = '';
    document.getElementById('appointmentDate').value = '2026-10-05';
    toggleVenueInput();
    validateAvailability();
}

function handleFormSubmit(event) {
    event.preventDefault();

    if (!validateAvailability()) {
        return;
    }

    const editId = document.getElementById('editAppointmentId').value;
    const lastName = document.getElementById('clientLastName').value;
    const firstName = document.getElementById('clientFirstName').value;
    const address = document.getElementById('clientAddress').value;
    const contactNo = document.getElementById('clientContactNo').value;
    const appointmentType = document.getElementById('appointmentType').value;
    const venue = appointmentType === 'Home service' ? document.getElementById('venueInput').value : 'Salon Address (Main Branch)';
    const serviceID = parseInt(document.getElementById('serviceSelect').value);
    const resourceID = parseInt(document.getElementById('resourceSelect').value);
    const appointmentDate = document.getElementById('appointmentDate').value;
    const startTime = document.getElementById('startTime').value;
    const endTime = document.getElementById('endTime').value;
    const paymentMethod = document.getElementById('paymentMethod').value;
    const paymentStatus = document.getElementById('paymentStatus').value;
    const appointmentNotes = document.getElementById('appointmentNotes').value;

    let client = db.clients.find(c => c.lastName.toLowerCase() === lastName.toLowerCase() && c.firstName.toLowerCase() === firstName.toLowerCase());
    let clientID;
    if (!client) {
        clientID = db.clients.length + 1;
        db.clients.push({ clientID, lastName, firstName, address, contactNo });
    } else {
        clientID = client.clientID;
    }

    const service = db.services.find(s => s.serviceID === serviceID);
    const totalCost = service ? service.basePrice : 500.00;

    if (editId) {
        const apt = db.appointments.find(a => a.appointmentID === editId);
        if (apt) {
            apt.clientID = clientID;
            apt.appointmentType = appointmentType;
            apt.venue = venue;
            apt.serviceID = serviceID;
            apt.resourceID = resourceID;
            apt.appointmentDate = appointmentDate;
            apt.startTime = startTime;
            apt.endTime = endTime;
            apt.paymentMethod = paymentMethod;
            apt.paymentStatus = paymentStatus;
            apt.totalCost = totalCost;
            apt.appointmentNotes = appointmentNotes;
        }
    } else {
        const newId = 'APT-' + (1000 + db.appointments.length + 1);
        db.appointments.push({
            appointmentID: newId,
            userID: 1,
            clientID,
            appointmentDate,
            startTime,
            endTime,
            appointmentType,
            venue,
            appointmentStatus: 'Confirmed',
            totalCost,
            paymentStatus,
            appointmentNotes,
            serviceID,
            resourceID
        });
    }

    const modalEl = document.getElementById('appointmentModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    modal.hide();
    renderAppointments();
}

function viewAppointment(id) {
    const apt = db.appointments.find(a => a.appointmentID === id);
    if (!apt) return;
    const client = db.clients.find(c => c.clientID === apt.clientID);
    const service = db.services.find(s => s.serviceID === apt.serviceID);
    const resource = db.resources.find(r => r.resourceID === apt.resourceID);

    document.getElementById('viewModalTitle').innerText = `Appointment Details (${apt.appointmentID})`;
    document.getElementById('viewModalBody').innerHTML = `
        <p><strong>Date & Time:</strong> ${apt.appointmentDate} (${apt.startTime} - ${apt.endTime})</p>
        <p><strong>Type:</strong> ${apt.appointmentType}</p>
        <p><strong>Venue:</strong> ${apt.venue}</p>
        <p><strong>Client:</strong> ${client.firstName} ${client.lastName} (${client.contactNo})</p>
        <p><strong>Service:</strong> ${service.serviceName} - ₱${apt.totalCost}</p>
        <p><strong>Assigned Beautician:</strong> ${resource.firstName} ${resource.lastName} (${resource.resourceType})</p>
        <p><strong>Payment Status:</strong> ${apt.paymentStatus}</p>
        <p><strong>Notes:</strong> ${apt.appointmentNotes || 'None'}</p>
    `;
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}

function editAppointment(id) {
    const apt = db.appointments.find(a => a.appointmentID === id);
    if (!apt) return;
    const client = db.clients.find(c => c.clientID === apt.clientID);

    document.getElementById('modalTitle').innerText = `Edit Appointment (${apt.appointmentID})`;
    document.getElementById('saveAppointmentBtn').innerText = 'Save Changes';
    document.getElementById('editAppointmentId').value = apt.appointmentID;

    document.getElementById('clientLastName').value = client.lastName;
    document.getElementById('clientFirstName').value = client.firstName;
    document.getElementById('clientAddress').value = client.address;
    document.getElementById('clientContactNo').value = client.contactNo;
    document.getElementById('appointmentType').value = apt.appointmentType;
    toggleVenueInput();
    document.getElementById('venueInput').value = apt.venue;
    document.getElementById('serviceSelect').value = apt.serviceID;
    document.getElementById('resourceSelect').value = apt.resourceID;
    document.getElementById('appointmentDate').value = apt.appointmentDate;
    document.getElementById('startTime').value = apt.startTime;
    document.getElementById('endTime').value = apt.endTime;
    document.getElementById('paymentStatus').value = apt.paymentStatus;
    document.getElementById('appointmentNotes').value = apt.appointmentNotes || '';

    validateAvailability();
    new bootstrap.Modal(document.getElementById('appointmentModal')).show();
}

function confirmDeleteAppointment(id) {
    deleteTargetId = id;
    document.getElementById('deleteAppointmentIdText').innerText = id;
    new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
}

function executeDeleteAppointment() {
    if (deleteTargetId) {
        db.appointments = db.appointments.filter(a => a.appointmentID !== deleteTargetId);
        deleteTargetId = null;
        const modalEl = document.getElementById('deleteConfirmModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();
        renderAppointments();
    }
}