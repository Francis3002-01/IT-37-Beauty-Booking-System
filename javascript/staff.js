// PHP renders staff records. JavaScript handles search, filters and modals.

function clearStaffValidation()
{
    const fields = ['staffFirstName', 'staffLastName', 'staffAddress', 'staffContactNo', 'dateSeparated'];
    for (let index = 0; index < fields.length; index++) {
        document.getElementById(fields[index]).setCustomValidity('');
    }
}

function validateStaffForm()
{
    clearStaffValidation();
    updateSeparationRequirement();
    const names = ['staffFirstName', 'staffLastName'];
    for (let index = 0; index < names.length; index++) {
        const field = document.getElementById(names[index]);
        const name = field.value.trim();
        if (!/\p{L}/u.test(name) || !/^[\p{L}\p{M} .’'\-]+$/u.test(name)) {
            field.setCustomValidity('Enter letters; spaces, apostrophes, periods and hyphens are allowed.');
        }
    }
    const address = document.getElementById('staffAddress');
    const text = address.value.trim();
    if (!/\p{L}/u.test(text) || !/^[\p{L}\p{M}\p{N} .,\r\n’'\-\/#():]+$/u.test(text)) {
        address.setCustomValidity('Enter an address containing letters, with ordinary numbers and punctuation.');
    }
    const contact = document.getElementById('staffContactNo');
    if (!/^[0-9]{11}$/.test(contact.value.trim())) {
        contact.setCustomValidity('Enter exactly 11 digits.');
    }
    const joined = document.getElementById('dateJoined').value;
    const separated = document.getElementById('dateSeparated');
    if (joined && separated.value && separated.value < joined) {
        separated.setCustomValidity('Date separated must be on or after date joined.');
    }
    return document.getElementById('staffForm').reportValidity();
}

//hide the success/error message after the page loads cz it keeps showing up whenever i reload dawg
function hideStaffFeedback()
{
    const feedback = document.getElementById('staffFeedback');
    if (feedback) feedback.style.display = 'none';
}

const staffFeedback = document.getElementById('staffFeedback');
if (staffFeedback) {
    setTimeout(hideStaffFeedback, Number(staffFeedback.getAttribute('data-duration')) || 3000);
}

//search and employment status filters work on the PHP-rendered cards
function filterStaff()
{
    const query = document.getElementById('searchInput').value.trim().toLowerCase();
    const employment = document.getElementById('employmentFilter').value;
    const records = document.getElementsByClassName('staff-record');
    let visibleCount = 0;
    for (let index = 0; index < records.length; index++) {
        const record = records[index];
        const type = record.getAttribute('data-type');
        const text = (record.getAttribute('data-first-name') + ' ' + record.getAttribute('data-last-name') + ' ' + type + ' ' + record.getAttribute('data-status') + ' ' + record.getAttribute('data-address') + ' ' + record.getAttribute('data-contact')).toLowerCase();
        if ((employment === 'All' || record.getAttribute('data-status') === employment) && text.includes(query)) {
            record.style.display = ''; visibleCount++;
        } else record.style.display = 'none';
    }
    document.getElementById('noStaffMessage').hidden = visibleCount > 0;
}
//reset the form for addition; ordinary form submission handles saving
function updateSeparationRequirement()
{
    const inactive = document.getElementById('staffStatus').value === 'Inactive';
    const separated = document.getElementById('dateSeparated');
    separated.required = inactive;
    separated.disabled = !inactive;
    if (!inactive) separated.value = '';
    separated.setCustomValidity('');
    document.getElementById('dateSeparatedLabel').textContent = inactive
        ? 'Date Separated (Required)' : 'Date Separated (Inactive only)';
}

function openCreateModal()
{
    document.getElementById('staffForm').reset();
    clearStaffValidation();
    updateSeparationRequirement();
    document.getElementById('editStaffId').value = '';
    document.getElementById('staffAction').value = 'create';
    document.getElementById('staffModalTitle').textContent = 'Add New Staff';
    document.getElementById('saveStaffBtn').textContent = 'Add Staff';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('staffModal')).show();
}
//fill the edit form with the selected record

function editStaff(id)
{
    const record = document.getElementById('staff-' + id);
    if (!record) return;
    document.getElementById('staffForm').reset();
    clearStaffValidation();
    document.getElementById('staffAction').value = 'edit';
    document.getElementById('editStaffId').value = id;
    document.getElementById('staffFirstName').value = record.getAttribute('data-first-name') || '';
    document.getElementById('staffLastName').value = record.getAttribute('data-last-name') || '';
    document.getElementById('staffUsername').value = record.getAttribute('data-username') || '';
    document.getElementById('staffAddress').value = record.getAttribute('data-address') || '';
    document.getElementById('staffContactNo').value = record.getAttribute('data-contact') || '';
    document.getElementById('staffType').value = record.getAttribute('data-type') || '';
    document.getElementById('staffStatus').value = record.getAttribute('data-status') || '';
    document.getElementById('dateJoined').value = record.getAttribute('data-joined') || '';
    document.getElementById('dateSeparated').value = record.getAttribute('data-separated') || '';
    updateSeparationRequirement();
    document.getElementById('staffModalTitle').textContent = 'Edit Staff';
    document.getElementById('saveStaffBtn').textContent = 'Save Changes';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('staffModal')).show();
}
//use textContent so displayed values cannot become HTML
function viewStaff(id)
{
    const record = document.getElementById('staff-' + id);
    if (!record) return;
    const photo = document.getElementById('viewStaffImage');
    const imagePath = record.getAttribute('data-image');
    photo.hidden = !imagePath;
    if (imagePath) photo.src = imagePath;
    else photo.removeAttribute('src');

    document.getElementById('viewStaffId').textContent = id;
    document.getElementById('viewStaffFirstName').textContent = record.getAttribute('data-first-name');
    document.getElementById('viewStaffLastName').textContent = record.getAttribute('data-last-name');
    document.getElementById('viewStaffAddress').textContent = record.getAttribute('data-address');
    document.getElementById('viewStaffContactNo').textContent = record.getAttribute('data-contact');
    document.getElementById('viewStaffType').textContent = record.getAttribute('data-type');
    document.getElementById('viewStaffStatus').textContent = record.getAttribute('data-status');
    document.getElementById('viewStaffDateJoined').textContent = record.getAttribute('data-joined');
    document.getElementById('viewStaffDateSeparated').textContent = record.getAttribute('data-separated') || '-';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('viewStaffModal')).show();
}
function confirmStaffDelete(staffID) {
    document.getElementById('deleteStaffId').value = staffID;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteStaffModal')).show();
}
filterStaff();
