/* =========================================================
   APPOINTMENTS MODULE
   Database-backed appointment display and form handling
   ========================================================= */

let currentFilter = 'All';
let availabilityRequest = 0;

document.addEventListener('DOMContentLoaded', () => {
    populateDropdowns();
    renderAppointments();
    toggleVenueInput();

    const form = document.getElementById('appointmentForm');

    if (form) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const available = await validateAvailability();

            if (!available) {
                alert(
                    'Please choose an available beautician, date, and time before saving.'
                );
                return;
            }

<<<<<<< HEAD
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

                    <div class="border-top pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="field-label">Service Cost</div>
                            <div class="fw-bold fs-6">₱${Number(apt.totalCost).toFixed(2)}</div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
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
=======
            // Submit the form to PHP after availability is confirmed.
            form.submit();
>>>>>>> 3414e15 (Login Functionality, and Appointment functionality)
        });
    }
});


/* =========================================================
   POPULATE FORM DROPDOWNS FROM MYSQL DATA
   ========================================================= */

function populateDropdowns() {
    const clientSelect = document.getElementById('clientSelect');
    const serviceSelect = document.getElementById('serviceSelect');
    const resourceSelect = document.getElementById('resourceSelect');

    // Clients
    clientSelect.innerHTML =
        '<option value="" disabled selected>Select a Client...</option>';

    (window.dbClients || []).forEach(client => {
        const option = document.createElement('option');

        option.value = client.clientID;
        option.textContent =
            `${client.lastName}, ${client.firstName}`;

        clientSelect.appendChild(option);
    });

    // Services
    serviceSelect.innerHTML =
        '<option value="" disabled selected>Select a Service...</option>';

    (window.dbServices || []).forEach(service => {
        const option = document.createElement('option');

        option.value = service.serviceID;
        option.textContent =
            `${service.serviceName} - ₱${Number(service.price).toFixed(2)}`;

        serviceSelect.appendChild(option);
    });

    // Active beauticians
    resourceSelect.innerHTML =
        '<option value="" disabled selected>Select a Beautician...</option>';

    (window.dbBeauticians || []).forEach(beautician => {
        const option = document.createElement('option');

        option.value = beautician.beauticianID;
        option.textContent =
            `${beautician.firstName} ${beautician.lastName} (${beautician.beauticianType})`;

        resourceSelect.appendChild(option);
    });
}


/* =========================================================
   AUTO-FILL CLIENT DETAILS
   ========================================================= */

function fillClientDetails() {
    const clientID = document.getElementById('clientSelect').value;

    const client = (window.dbClients || []).find(
        item => String(item.clientID) === String(clientID)
    );

    document.getElementById('clientLastName').value =
        client ? client.lastName : '';

    document.getElementById('clientFirstName').value =
        client ? client.firstName : '';

    document.getElementById('clientAddress').value =
        client ? client.address : '';

    document.getElementById('clientContactNo').value =
        client ? client.contactNo : '';
}


/* =========================================================
   APPOINTMENT TYPE AND VENUE
   ========================================================= */

function toggleVenueInput() {
    const appointmentType =
        document.getElementById('appointmentType').value;

    const venueWrapper =
        document.getElementById('venueWrapper');

    const venueInput =
        document.getElementById('venueInput');

    if (appointmentType === 'Home service') {
        venueWrapper.style.display = 'block';
        venueInput.required = true;
    } else {
        venueWrapper.style.display = 'none';
        venueInput.required = false;
        venueInput.value = '';
    }
}


/* =========================================================
   OPEN CREATE APPOINTMENT MODAL
   ========================================================= */

function openCreateModal() {
    const form = document.getElementById('appointmentForm');

    form.reset();

    document.getElementById('editAppointmentId').value = '';

    document.getElementById('modalTitle').textContent =
        'Create New Appointment';

    document.getElementById('saveAppointmentBtn').textContent =
        'Create Appointment';

    toggleVenueInput();

    document.getElementById('conflictBanner').textContent =
        'Conflict Check: Select beautician, date and time';
}


/* =========================================================
   SERVICE PRICE
   Price is calculated and saved by the PHP backend.
   ========================================================= */

function updateServiceCost() {
    // The PHP backend retrieves the actual price from MySQL.
}


/* =========================================================
   CHECK BEAUTICIAN AVAILABILITY THROUGH PHP
   ========================================================= */

async function validateAvailability() {
    const requestId = ++availabilityRequest;

    const beauticianID =
        document.getElementById('resourceSelect').value;

    const appointmentDate =
        document.getElementById('appointmentDate').value;

    const startTime =
        document.getElementById('startTime').value;

    const endTime =
        document.getElementById('endTime').value;

    const editID =
        document.getElementById('editAppointmentId').value;

    const banner =
        document.getElementById('conflictBanner');

    function showBanner(message, available) {
        banner.style.backgroundColor =
            available ? '#e6fcf5' : '#fff5f5';

        banner.style.color =
            available ? '#0ca678' : '#fa5252';

        banner.textContent = 'Conflict Check: ' + message;
    }

    if (!beauticianID || !appointmentDate || !startTime || !endTime) {
        showBanner('Select beautician, date and time', false);
        return false;
    }

    if (startTime >= endTime) {
        showBanner('End time must be after start time', false);
        return false;
    }

    showBanner('Checking availability...', false);

    try {
        const response = await fetch(
            'backend%20logic/appointment_backend.php',
            {
                method: 'POST',

                headers: {
                    'Content-Type':
                        'application/x-www-form-urlencoded;charset=UTF-8'
                },

                body: new URLSearchParams({
                    action: 'check_availability',
                    beauticianID: beauticianID,
                    appointmentDate: appointmentDate,
                    startTime: startTime,
                    endTime: endTime,
                    editAppointmentId: editID
                })
            }
        );

        if (!response.ok) {
            throw new Error('Availability request failed');
        }

        const result = await response.json();

        // Ignore outdated requests.
        if (requestId !== availabilityRequest) {
            return false;
        }

        showBanner(
            result.message || 'Availability checked',
            Boolean(result.available)
        );

        return Boolean(result.available);

    } catch (error) {
        if (requestId === availabilityRequest) {
            showBanner('Unable to check database availability', false);
        }

        console.error('Availability check error:', error);

        return false;
    }
}


/* =========================================================
   RENDER APPOINTMENTS RETRIEVED FROM MYSQL
   ========================================================= */

function renderAppointments(list = window.dbAppointments || []) {
    const grid = document.getElementById('appointmentsGrid');

    if (!grid) {
        return;
    }

    if (!list.length) {
        grid.innerHTML = `
            <div class="col-12 text-center text-muted py-5">
                No appointments found.
            </div>
        `;
        return;
    }

    grid.innerHTML = list.map(apt => {
        const paymentStatus = apt.paymentStatus || 'Unpaid';

        const paymentBadgeClass =
            paymentStatus === 'Paid' ? 'badge-paid' : 'badge-unpaid';

        const clientName =
            `${apt.clientFirstName || 'Unknown'} ${apt.clientLastName || 'Client'}`;

        const beauticianName =
            `${apt.beauticianFirstName || 'Unassigned'} ${apt.beauticianLastName || ''}`;

        const venue =
            apt.venue || 'Salon Address (Main Branch)';

        const notes =
            apt.appointmentNotes || 'None';

        return `
            <div class="col-md-6 mb-3">
                <div class="appointment-item-card">

                    <div class="d-flex justify-content-between
                                align-items-start mb-3 flex-wrap gap-2">

                        <div>
                            <h5 class="fw-bold mb-1">
                                APT-${escapeHtml(apt.appointmentID)}
                            </h5>

                            <div class="text-muted" style="font-size:.85rem">
                                ${escapeHtml(apt.appointmentDate)}
                                |
                                ${escapeHtml(apt.startTime)}
                                -
                                ${escapeHtml(apt.endTime)}
                            </div>
                        </div>

                        <div class="d-flex gap-2 align-items-center">
                            <span class="${paymentBadgeClass}">
                                ${escapeHtml(paymentStatus)}
                            </span>

                            <span class="badge-status">
                                ${escapeHtml(apt.appointmentStatus)}
                            </span>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">

                        <div class="col-6">
                            <div class="field-label">Appointment Type</div>
                            <div class="field-value">
                                ${escapeHtml(apt.appointmentType)}
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="field-label">Service Type</div>
                            <div class="field-value">
                                ${escapeHtml(apt.serviceCategory || 'General')}
                                (${escapeHtml(apt.serviceName || 'Service')})
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="field-label">Venue</div>
                            <div class="field-value">
                                ${escapeHtml(venue)}
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="field-label">Client</div>
                            <div class="field-value">
                                ${escapeHtml(clientName)}
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="field-label">Assigned Beautician</div>
                            <div class="field-value">
                                ${escapeHtml(beauticianName)}
                                (${escapeHtml(apt.beauticianType || '')})
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="field-label">Appointment Notes</div>
                            <div class="field-value text-muted">
                                ${escapeHtml(notes)}
                            </div>
                        </div>

                    </div>

                    <div class="border-top pt-3 d-flex
                                justify-content-between align-items-center
                                flex-wrap gap-2">

                        <div>
                            <div class="field-label">Service Cost</div>

                            <div class="fw-bold fs-6">
                                ₱${Number(apt.totalCost || 0).toFixed(2)}
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-dark px-3 rounded-pill"
                            onclick="viewAppointment(${Number(apt.appointmentID)})"
                        >
                            VIEW
                        </button>

                    </div>

                </div>
            </div>
        `;
    }).join('');
}


/* =========================================================
   ESCAPE DATABASE VALUES BEFORE DISPLAYING THEM
   ========================================================= */

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[char]);
}


/* =========================================================
   STATUS FILTERS
   ========================================================= */

function setFilter(status, button) {
    document.querySelectorAll('.filter-pill').forEach(pill => {
        pill.classList.remove('active');
    });

    button.classList.add('active');

    currentFilter = status;

    filterAppointments();
}


/* =========================================================
   SEARCH AND FILTER DATABASE APPOINTMENTS
   ========================================================= */

function filterAppointments() {
    const searchInput = document.getElementById('searchInput');

    const query = searchInput.value.trim().toLowerCase();

    let list = window.dbAppointments || [];

    if (currentFilter !== 'All') {
        list = list.filter(apt =>
            apt.appointmentStatus === currentFilter
        );
    }

    if (query) {
        list = list.filter(apt => {
            const searchableValues = [
                apt.appointmentID,
                apt.clientFirstName,
                apt.clientLastName,
                apt.serviceName,
                apt.beauticianFirstName,
                apt.beauticianLastName,
                apt.venue,
                apt.appointmentType
            ];

            return searchableValues.some(value =>
                String(value || '').toLowerCase().includes(query)
            );
        });
    }

    renderAppointments(list);
}


/* =========================================================
   VIEW APPOINTMENT DETAILS
   ========================================================= */

function viewAppointment(id) {
    const apt = (window.dbAppointments || []).find(
        item => Number(item.appointmentID) === Number(id)
    );

    if (!apt) {
        return;
    }

    const body = document.getElementById('viewModalBody');

    document.getElementById('viewModalTitle').textContent =
        `Appointment Details (APT-${apt.appointmentID})`;

    body.innerHTML = `
        <p>
            <strong>Date & Time:</strong>
            ${escapeHtml(apt.appointmentDate)}
            (${escapeHtml(apt.startTime)} - ${escapeHtml(apt.endTime)})
        </p>

        <p>
            <strong>Status:</strong>
            ${escapeHtml(apt.appointmentStatus)}
        </p>

        <p>
            <strong>Type:</strong>
            ${escapeHtml(apt.appointmentType)}
        </p>

        <p>
            <strong>Venue:</strong>
            ${escapeHtml(apt.venue || 'Salon Address (Main Branch)')}
        </p>

        <p>
            <strong>Client:</strong>
            ${escapeHtml(apt.clientFirstName)}
            ${escapeHtml(apt.clientLastName)}
        </p>

        <p>
            <strong>Service:</strong>
            ${escapeHtml(apt.serviceName)}
            — ₱${Number(apt.totalCost || 0).toFixed(2)}
        </p>

        <p>
            <strong>Beautician:</strong>
            ${escapeHtml(apt.beauticianFirstName)}
            ${escapeHtml(apt.beauticianLastName)}
        </p>

        <p>
            <strong>Payment:</strong>
            ${escapeHtml(apt.paymentStatus || 'Unpaid')}
        </p>

        <p>
            <strong>Notes:</strong>
            ${escapeHtml(apt.appointmentNotes || 'None')}
        </p>
    `;

    new bootstrap.Modal(
        document.getElementById('viewModal')
    ).show();
}