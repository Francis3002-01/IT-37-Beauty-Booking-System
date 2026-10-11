// PHP renders beautician records. JavaScript handles search, filters and modals.

function clearBeauticianValidation()
{
    const fields = ['beauticianFirstName', 'beauticianLastName', 'beauticianAddress', 'beauticianContactNo', 'dateSeparated'];
    for (let index = 0; index < fields.length; index++) {
        document.getElementById(fields[index]).setCustomValidity('');
    }
}

function validateBeauticianForm()
{
    clearBeauticianValidation();
    const names = ['beauticianFirstName', 'beauticianLastName'];
    for (let index = 0; index < names.length; index++) {
        const field = document.getElementById(names[index]);
        const name = field.value.trim();
        if (!/\p{L}/u.test(name) || !/^[\p{L}\p{M} .’'\-]+$/u.test(name)) {
            field.setCustomValidity('Enter letters; spaces, apostrophes, periods and hyphens are allowed.');
        }
    }
    const address = document.getElementById('beauticianAddress');
    const text = address.value.trim();
    if (!/\p{L}/u.test(text) || !/^[\p{L}\p{M}\p{N} .,\r\n’'\-\/#():]+$/u.test(text)) {
        address.setCustomValidity('Enter an address containing letters, with ordinary numbers and punctuation.');
    }
    const contact = document.getElementById('beauticianContactNo');
    if (!/^[0-9]{11}$/.test(contact.value.trim())) {
        contact.setCustomValidity('Enter exactly 11 digits.');
    }
    const joined = document.getElementById('dateJoined').value;
    const separated = document.getElementById('dateSeparated');
    if (joined && separated.value && separated.value < joined) {
        separated.setCustomValidity('Date separated must be on or after date joined.');
    }
    return document.getElementById('beauticianForm').reportValidity();
}

//hide the success/error message after the page loads cz it keeps showing up whenever i reload dawg
function hideBeauticianFeedback()
{
    const feedback = document.getElementById('beauticianFeedback');
    if (feedback) feedback.style.display = 'none';
}

setTimeout(hideBeauticianFeedback, 3000); //set the timeout to 3 seconds after notifying outcomes

//search and specialization filters work on the PHP-rendered cards
let currentFilter = 'All';
function setFilter(type, button)
{
    const buttons = document.getElementsByClassName('filter-pill');
    for (let index = 0; index < buttons.length; index++) buttons[index].classList.remove('active');
    button.classList.add('active');
    currentFilter = type;
    filterBeauticians();
}
function filterBeauticians()
{
    const query = document.getElementById('searchInput').value.trim().toLowerCase();
    const records = document.getElementsByClassName('beautician-record');
    let visibleCount = 0;
    for (let index = 0; index < records.length; index++) {
        const record = records[index];
        const type = record.getAttribute('data-type');
        const text = (record.getAttribute('data-first-name') + ' ' + record.getAttribute('data-last-name') + ' ' + type + ' ' + record.getAttribute('data-status') + ' ' + record.getAttribute('data-address') + ' ' + record.getAttribute('data-contact')).toLowerCase();
        if ((currentFilter === 'All' || type === currentFilter) && text.includes(query)) {
            record.style.display = ''; visibleCount++;
        } else record.style.display = 'none';
    }
    document.getElementById('noBeauticiansMessage').hidden = visibleCount > 0;
}
//reset the form for addition; ordinary form submission handles saving
function openCreateModal()
{
    document.getElementById('beauticianForm').reset();
    clearBeauticianValidation();
    document.getElementById('editBeauticianId').value = '';
    document.getElementById('beauticianAction').value = 'create';
    document.getElementById('beauticianModalTitle').textContent = 'Add New Beautician';
    document.getElementById('saveBeauticianBtn').textContent = 'Add Beautician';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('beauticianModal')).show();
}
//fill the edit form with the selected record
function editBeautician(id)
{
    const record = document.getElementById('beautician-' + id);
    if (!record) return;
    document.getElementById('beauticianForm').reset();
    clearBeauticianValidation();
    document.getElementById('editBeauticianId').value = id;
    document.getElementById('beauticianAction').value = 'edit';
    document.getElementById('beauticianFirstName').value = record.getAttribute('data-first-name');
    document.getElementById('beauticianLastName').value = record.getAttribute('data-last-name');
    document.getElementById('beauticianAddress').value = record.getAttribute('data-address');
    document.getElementById('beauticianContactNo').value = record.getAttribute('data-contact');
    document.getElementById('beauticianType').value = record.getAttribute('data-type');
    document.getElementById('beauticianStatus').value = record.getAttribute('data-status');
    document.getElementById('dateJoined').value = record.getAttribute('data-joined');
    document.getElementById('dateSeparated').value = record.getAttribute('data-separated');
    document.getElementById('beauticianModalTitle').textContent = 'Edit Beautician';
    document.getElementById('saveBeauticianBtn').textContent = 'Save Changes';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('beauticianModal')).show();
}
//use textContent so displayed values cannot become HTML
function viewBeautician(id)
{
    const record = document.getElementById('beautician-' + id);
    if (!record) return;
    document.getElementById('viewBeauticianId').textContent = id;
    document.getElementById('viewBeauticianFirstName').textContent = record.getAttribute('data-first-name');
    document.getElementById('viewBeauticianLastName').textContent = record.getAttribute('data-last-name');
    document.getElementById('viewBeauticianAddress').textContent = record.getAttribute('data-address');
    document.getElementById('viewBeauticianContactNo').textContent = record.getAttribute('data-contact');
    document.getElementById('viewBeauticianType').textContent = record.getAttribute('data-type');
    document.getElementById('viewBeauticianStatus').textContent = record.getAttribute('data-status');
    document.getElementById('viewBeauticianDateJoined').textContent = record.getAttribute('data-joined');
    document.getElementById('viewBeauticianDateSeparated').textContent = record.getAttribute('data-separated') || '-';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('viewBeauticianModal')).show();
}
function confirmBeauticianDelete()
{
    return confirm('Request deletion of this beautician? Records used in appointments cannot be deleted.');
}
filterBeauticians();
