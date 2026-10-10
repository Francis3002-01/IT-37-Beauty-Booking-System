// PHP renders client records. JavaScript handles search, sorting and modals.

function clearClientValidation()
{
    const fields = ['clientFirstName', 'clientLastName', 'clientAddress', 'clientContactNo'];
    for (let index = 0; index < fields.length; index++) {
        document.getElementById(fields[index]).setCustomValidity('');
    }
}

function validateClientForm()
{
    clearClientValidation();
    const names = ['clientFirstName', 'clientLastName'];
    for (let index = 0; index < names.length; index++) {
        const field = document.getElementById(names[index]);
        const name = field.value.trim();
        if (!/\p{L}/u.test(name) || !/^[\p{L}\p{M} .’'\-]+$/u.test(name)) {
            field.setCustomValidity('Enter letters; spaces, apostrophes, periods and hyphens are allowed.');
        }
    }
    const address = document.getElementById('clientAddress');
    const text = address.value.trim();
    if (!/\p{L}/u.test(text) || !/^[\p{L}\p{M}\p{N} .,\r\n’'\-\/#():]+$/u.test(text)) {
        address.setCustomValidity('Enter an address containing letters, with ordinary numbers and punctuation.');
    }
    const contact = document.getElementById('clientContactNo');
    if (!/^[0-9]{11}$/.test(contact.value.trim())) {
        contact.setCustomValidity('Enter exactly 11 digits.');
    }
    return document.getElementById('clientForm').reportValidity();
}

//hide the success/error message after the page loads cz it keeps showing up whenever i reload dawg
function hideClientFeedback()
{
    const feedback = document.getElementById('clientFeedback');
    if (feedback) feedback.style.display = 'none';
}

setTimeout(hideClientFeedback, 3000); //set the timeout to 3 seconds after notifying outcomes
 
function filterClients() //filter services based on search input
{
    const query = document.getElementById('searchInput').value.trim().toLowerCase();
    const records = document.getElementsByClassName('client-record');
    let visibleCount = 0;
    for (let index = 0; index < records.length; index++) {
        const record = records[index];
        const text = (record.getAttribute('data-first-name') + ' ' + record.getAttribute('data-last-name')
            + ' ' + record.getAttribute('data-address') + ' ' + record.getAttribute('data-contact')).toLowerCase();
        if (text.includes(query)) {
            record.style.display = '';
            visibleCount++;
        } else {
            record.style.display = 'none';
        }
    }
    document.getElementById('noClientsMessage').hidden = visibleCount > 0;
}

function clientComesBefore(first, second, sortType) //sort client list from A-Z or by recent addition
{
    if (sortType === 'AZ') {
        const firstName = first.getAttribute('data-first-name') + ' ' + first.getAttribute('data-last-name');
        const secondName = second.getAttribute('data-first-name') + ' ' + second.getAttribute('data-last-name');
        return firstName.localeCompare(secondName) < 0;
    }
    // The table has no creation date; newer auto-increment IDs come first.
    if (sortType === 'Recent') {
        return Number(first.getAttribute('data-id')) > Number(second.getAttribute('data-id'));
    }
    return Number(first.getAttribute('data-id')) < Number(second.getAttribute('data-id'));
}

function setFilter(sortType, button)
{
    const buttons = document.getElementsByClassName('filter-pill');
    for (let index = 0; index < buttons.length; index++) buttons[index].classList.remove('active');
    button.classList.add('active');
    const records = document.getElementsByClassName('client-record');
    const sorted = [];
    // Insertion sort keeps the code step-by-step without complex callbacks.
    for (let index = 0; index < records.length; index++) {
        const record = records[index];
        let position = sorted.length;
        while (position > 0 && clientComesBefore(record, sorted[position - 1], sortType)) {
            sorted[position] = sorted[position - 1];
            position--;
        }
        sorted[position] = record;
    }
    const list = document.getElementById('clientsList');
    for (let index = 0; index < sorted.length; index++) list.appendChild(sorted[index]);
    filterClients();
}

function openCreateModal()
{
    document.getElementById('clientForm').reset();
    clearClientValidation();
    document.getElementById('editClientId').value = '';
    document.getElementById('clientAction').value = 'create';
    document.getElementById('clientModalTitle').textContent = 'Add New Client';
    document.getElementById('saveClientBtn').textContent = 'Add Client';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('clientModal')).show();
}
function editClient(id)
{
    const record = document.getElementById('client-' + id);
    if (!record) return;
    document.getElementById('clientForm').reset();
    clearClientValidation();
    document.getElementById('editClientId').value = id;
    document.getElementById('clientAction').value = 'edit';
    document.getElementById('clientFirstName').value = record.getAttribute('data-first-name');
    document.getElementById('clientLastName').value = record.getAttribute('data-last-name');
    document.getElementById('clientAddress').value = record.getAttribute('data-address');
    document.getElementById('clientContactNo').value = record.getAttribute('data-contact');
    document.getElementById('clientModalTitle').textContent = 'Edit Client';
    document.getElementById('saveClientBtn').textContent = 'Save Changes';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('clientModal')).show();
}
function confirmClientDelete()
{
    return confirm('Request deletion of this client? Clients used in appointments cannot be deleted.');
}
filterClients();
