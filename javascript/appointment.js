
/* =========================================================
   APPOINTMENTS MODULE
   Database-backed appointment display and form handling
   ========================================================= */

let currentFilter = 'All';
let availabilityRequest = 0;

/* =========================================================
   INITIALIZE MODULE
   ========================================================= */

document.addEventListener('DOMContentLoaded', () => {
    populateDropdowns();
    renderAppointments();
    toggleVenueInput();

    const form = document.getElementById('appointmentForm');

    if (form) {
        form.addEventListener('submit', handleAppointmentSubmit);
    }

    const appointmentType = document.getElementById('appointmentType');

    if (appointmentType) {
        appointmentType.addEventListener('change', toggleVenueInput);
    }

    const searchInput = document.getElementById('searchInput');

    if (searchInput) {
        searchInput.addEventListener('input', filterAppointments);
    }
});

/* =========================================================
   APPOINTMENT FORM SUBMISSION
   ========================================================= */

async function handleAppointmentSubmit(event) {
    event.preventDefault();

    const available = await validateAvailability();

    if (!available) {
        alert(
            'Please choose an available beautician, date, and time before saving.'
        );
        return;
    }

    /*
     * IMPORTANT:
     * The PHP save/update request is not included in the original code.
     * Add that request here once its action and expected fields are confirmed.
     */
    alert(
        'Availability confirmed. The appointment save request still needs to be connected to the PHP backend.'
    );
}

/* =========================================================
   POPULATE FORM DROPDOWNS FROM MYSQL DATA
   ========================================================= */

function populateDropdowns() {
    const clientSelect = document.getElementById('clientSelect');
    const serviceSelect = document.getElementById('serviceSelect');
    const resourceSelect = document.getElementById('resourceSelect');

    // Clients
    if (clientSelect) {
        clientSelect.innerHTML =
            '<option value="" disabled selected>Select a Client...</option>';

        (window.dbClients || []).forEach(client => {
            const option = document.createElement('option');

            option.value = client.clientID;
            option.textContent =
                `${client.lastName || ''}, ${client.firstName || ''}`;

            clientSelect.appendChild(option);
        });
    }

    // Services
    if (serviceSelect) {
        serviceSelect.innerHTML =
            '<option value="" disabled selected>Select a Service...</option>';

        (window.dbServices || []).forEach(service => {
            const option = document.createElement('option');

            option.value = service.serviceID;
            option.textContent =
                `${service.serviceName} - ₱${Number(service.price || 0).toFixed(2)}`;

            serviceSelect.appendChild(option);
        });
    }

    // Beauticians
    if (resourceSelect) {
        resourceSelect.innerHTML =
            '<option value="" disabled selected>Select a Beautician...</option>';

        (window.dbBeauticians || []).forEach(beautician => {
            const option = document.createElement('option');

            option.value = beautician.beauticianID;
            option.textContent =
                `${beautician.firstName || ''} ${beautician.lastName || ''} (${beautician.beauticianType || ''})`;

            resourceSelect.appendChild(option);
        });
    }
}

/* =========================================================
   AUTO-FILL CLIENT DETAILS
   ========================================================= */

function fillClientDetails() {
    const clientSelect = document.getElementById('clientSelect');

    if (!clientSelect) {
        return;
    }

    const clientID = clientSelect.value;

    const client = (window.dbClients || []).find(
        item => String(item.clientID) === String(clientID)
    );

    const fields = {
        clientLastName: client ? client.lastName : '',
        clientFirstName: client ? client.firstName : '',
        clientAddress: client ? client.address : '',
        clientContactNo: client ? client.contactNo : ''
    };

    Object.entries(fields).forEach(([id, value]) => {
        const field = document.getElementById(id);

        if (field) {
            field.value = value || '';
        }
    });
}

/* =========================================================
   APPOINTMENT TYPE AND VENUE
   ========================================================= */

function toggleVenueInput() {
    const appointmentType = document.getElementById('appointmentType');
    const venueWrapper = document.getElementById('venueWrapper');
    const venueInput = document.getElementById('venueInput');

    if (!appointmentType || !venueWrapper || !venueInput) {
        return;
    }

    if (appointmentType.value === 'Home service') {
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

    if (!form) {
        return;
    }

    form.reset();

    const editID = document.getElementById('editAppointmentId');
    const modalTitle = document.getElementById('modalTitle');
    const saveButton = document.getElementById('saveAppointmentBtn');
    const conflictBanner = document.getElementById('conflictBanner');

    if (editID) {
        editID.value = '';
    }

    if (modalTitle) {
        modalTitle.textContent = 'Create New Appointment';
    }

    if (saveButton) {
        saveButton.textContent = 'Create Appointment';
    }

    if (conflictBanner) {
        conflictBanner.textContent =
            'Conflict Check: Select beautician, date and time';
    }

    toggleVenueInput();
}

/* =========================================================
   SERVICE PRICE
   Price is calculated by the PHP backend.
   ========================================================= */

function updateServiceCost() {
    // The PHP backend should retrieve the actual service price from MySQL.
}

/* =========================================================
   CHECK BEAUTICIAN AVAILABILITY THROUGH PHP
   ========================================================= */

async function validateAvailability() {
    const requestId = ++availabilityRequest;

    const beauticianField = document.getElementById('resourceSelect');
    const dateField = document.getElementById('appointmentDate');
    const startField = document.getElementById('startTime');
    const endField = document.getElementById('endTime');
    const editField = document.getElementById('editAppointmentId');
    const banner = document.getElementById('conflictBanner');

    const beauticianID = beauticianField ? beauticianField.value : '';
    const appointmentDate = dateField ? dateField.value : '';
    const startTime = startField ? startField.value : '';
    const endTime = endField ? endField.value : '';
    const editID = editField ? editField.value : '';

    function showBanner(message, available) {
        if (!banner) {
            return;
        }

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
                    beauticianID,
                    appointmentDate,
                    startTime,
                    endTime,
                    editAppointmentId: editID
                })
            }
        );

        if (!response.ok) {
            throw new Error(
                `Availability request failed: HTTP ${response.status}`
            );
        }

        const result = await response.json();

        // Ignore outdated availability responses.
        if (requestId !== availabilityRequest) {
            return false;
        }

        const available = result.available === true ||
            result.available === 1 ||
            result.available === '1';

        showBanner(
            result.message || 'Availability checked',
            available
        );

        return available;

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
        console.error('Appointments grid element was not found.');
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

        const appointmentID = Number(apt.appointmentID);

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
                                ${escapeHtml(apt.appointmentStatus || 'Pending')}
                            </span>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">

                        <div class="col-6">
                            <div class="field-label">Appointment Type</div>
                            <div class="field-value">
                                ${escapeHtml(apt.appointmentType || 'Not specified')}
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
                            onclick="viewAppointment(${Number.isFinite(appointmentID) ? appointmentID : 0})"
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

    if (button) {
        button.classList.add('active');
    }

    currentFilter = status;

    filterAppointments();
}

/* =========================================================
   SEARCH AND FILTER DATABASE APPOINTMENTS
   ========================================================= */

function filterAppointments() {
    const searchInput = document.getElementById('searchInput');

    const query = searchInput
        ? searchInput.value.trim().toLowerCase()
        : '';

    let filtered = window.dbAppointments || [];

    if (currentFilter !== 'All') {
        filtered = filtered.filter(
            apt => apt.appointmentStatus === currentFilter
        );
    }

    if (query !== '') {
        filtered = filtered.filter(apt => {
            const clientName =
                `${apt.clientFirstName || ''} ${apt.clientLastName || ''}`
                    .toLowerCase();

            return (
                String(apt.appointmentID || '').toLowerCase().includes(query) ||
                clientName.includes(query) ||
                String(apt.venue || '').toLowerCase().includes(query) ||
                String(apt.appointmentType || '').toLowerCase().includes(query)
            );
        });
    }

    renderAppointments(filtered);
}

/* =========================================================
   VIEW APPOINTMENT DETAILS
   ========================================================= */

function viewAppointment(id) {
    const appointments = window.dbAppointments || [];

    const apt = appointments.find(
        item => Number(item.appointmentID) === Number(id)
    );

    if (!apt) {
        console.error('Appointment not found:', id);
        return;
    }

    const body = document.getElementById('viewModalBody');
    const title = document.getElementById('viewModalTitle');
    const modalElement = document.getElementById('viewModal');

    if (!body || !title || !modalElement) {
        console.error('Appointment details modal elements were not found.');
        return;
    }

    title.textContent =
        `Appointment Details (APT-${apt.appointmentID})`;

    body.innerHTML = `
        <p>
            <strong>Date &amp; Time:</strong>
            ${escapeHtml(apt.appointmentDate)}
            (${escapeHtml(apt.startTime)} - ${escapeHtml(apt.endTime)})
        </p>

        <p>
            <strong>Status:</strong>
            ${escapeHtml(apt.appointmentStatus || 'Pending')}
        </p>

        <p>
            <strong>Type:</strong>
            ${escapeHtml(apt.appointmentType || 'Not specified')}
        </p>

        <p>
            <strong>Venue:</strong>
            ${escapeHtml(apt.venue || 'Salon Address (Main Branch)')}
        </p>

        <p>
            <strong>Client:</strong>
            ${escapeHtml(apt.clientFirstName || 'Unknown')}
            ${escapeHtml(apt.clientLastName || 'Client')}
        </p>

        <p>
            <strong>Service:</strong>
            ${escapeHtml(apt.serviceName || 'Service')}
            — ₱${Number(apt.totalCost || 0).toFixed(2)}
        </p>

        <p>
            <strong>Beautician:</strong>
            ${escapeHtml(apt.beauticianFirstName || 'Unassigned')}
            ${escapeHtml(apt.beauticianLastName || '')}
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

    bootstrap.Modal.getOrCreateInstance(modalElement).show();
}
