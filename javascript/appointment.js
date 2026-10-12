//PHP renders bookings and checks availability through ordinary form submissions.
function filterAppointments() {
    const query = document.getElementById('searchInput').value.trim().toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const date = document.getElementById('dateFilter').value;
    const records = document.getElementsByClassName('appointment-record');
    let visible = 0;
    for (let i = 0; i < records.length; i++) {
        const record = records[i];
        const matches =
            (status === 'All' || record.getAttribute('data-status') === status) &&
            (!date || record.getAttribute('data-date') === date) &&
            record.textContent.toLowerCase().includes(query);
        record.hidden = !matches;
        if (matches) visible++;
    }
    document.getElementById('noAppointmentsMessage').hidden = visible > 0;
}
function clearAppointmentFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = 'All';
    document.getElementById('dateFilter').value = '';
    filterAppointments();
}
function invalidateAvailability() {
    document.getElementById('appointmentBeautician').disabled = true;
    document.getElementById('saveAppointmentBtn').disabled = true;
}
function updateAppointmentPrice() {
    const select = document.getElementById('appointmentService');
    const option = select.options[select.selectedIndex];
    document.getElementById('serviceDuration').value = option.value
        ? option.getAttribute('data-duration') + ' minutes'
        : '';
    const amountPaid = document.getElementById('bookingAmountPaid');
    if (amountPaid) {
        amountPaid.max = option.value ? option.getAttribute('data-price') : '0';
        checkPaidAmount(amountPaid);
    }
    document.getElementById('appointmentPrice').textContent =
        'Price: PHP ' +
        Number(option.value ? option.getAttribute('data-price') : 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
}
function suggestAppointmentEnd() {
    const select = document.getElementById('appointmentService');
    const start = document.getElementById('startTime').value;
    const duration = Number(select.options[select.selectedIndex].getAttribute('data-duration'));
    if (start && duration) {
        const parts = start.split(':');
        const end = Number(parts[0]) * 60 + Number(parts[1]) + duration;
        document.getElementById('endTime').value =
            end < 1440
                ? String(Math.floor(end / 60)).padStart(2, '0') +
                  ':' +
                  String(end % 60).padStart(2, '0')
                : '';
    }
    updateAppointmentPrice();
    invalidateAvailability();
}
function toggleVenueInput() {
    const home = document.getElementById('appointmentType').value === 'Home service';
    document.getElementById('venueWrapper').hidden = !home;
    document.getElementById('venueInput').required = home;
}
function initializeAppointmentForm() {
    toggleVenueInput();
    updateAppointmentPrice();
    filterAppointments();
    const modal = document.getElementById('appointmentModal');
    if (modal.getAttribute('data-open') === 'yes')
        bootstrap.Modal.getOrCreateInstance(modal).show();
}
document.addEventListener('DOMContentLoaded', initializeAppointmentForm);

const appointmentFeedback = document.getElementById('appointmentFeedback');
if (appointmentFeedback && appointmentFeedback.getAttribute('data-status') === 'success') {
    setTimeout(function () {
        appointmentFeedback.hidden = true;
    }, 3000);
}

function checkPaidAmount(field) {
    field.setCustomValidity('');
    if (field.value === '') return;
    const amount = Number(field.value);
    if (!Number.isFinite(amount) || amount < Number(field.min) || amount > Number(field.max)) {
        field.setCustomValidity(
            'Amount paid must be greater than 0 and equal to or less than the price or balance.'
        );
    }
}
