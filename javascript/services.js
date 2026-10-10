//PHP renders the records. JavaScript handles filters and modal fields only.
let currentFilter = 'All';

//hide the success/error message after the page loads cz it keeps showing up whenever i reload dawg
function hideServiceFeedback()
{
    const feedback = document.getElementById('serviceFeedback');
    if (feedback) {
        feedback.style.display = 'none';
    }
}

setTimeout(hideServiceFeedback, 3000); //set the timeout to 3 seconds after notifying outcomes

function setFilter(category, button)
{
    const buttons = document.getElementsByClassName('filter-pill');
    for (let index = 0; index < buttons.length; index++) {
        buttons[index].classList.remove('active');
    }
    button.classList.add('active');
    currentFilter = category;
    filterServices();
}

function filterServices() //filter services based on search input and category filter
{
    const query = document.getElementById('searchInput').value.trim().toLowerCase();
    const records = document.getElementsByClassName('service-record');
    let visibleCount = 0;
    for (let index = 0; index < records.length; index++) {
        const record = records[index];
        const category = record.getAttribute('data-category');
        const text = (record.getAttribute('data-name') + ' ' + category + ' '
            + record.getAttribute('data-description')).toLowerCase();
        if ((currentFilter === 'All' || category === currentFilter) && text.includes(query)) {
            record.hidden = false;
            record.style.display = '';
            visibleCount++;
        } else {
            record.hidden = true;
            record.style.display = 'none';
        }
    }
    document.getElementById('noServicesMessage').hidden = visibleCount > 0;
}

filterServices();

function openCreateModal()
{
    document.getElementById('serviceForm').reset();
    document.getElementById('editServiceId').value = '';
    document.getElementById('serviceAction').value = 'create';
    document.getElementById('modalTitle').textContent = 'Add New Service';
    document.getElementById('saveServiceBtn').textContent = 'Add Service';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('serviceModal')).show();
}

function editService(id)
{
    const record = document.getElementById('service-' + id);
    if (!record) return;
    document.getElementById('serviceForm').reset();
    document.getElementById('editServiceId').value = id;
    document.getElementById('serviceAction').value = 'edit';
    document.getElementById('serviceName').value = record.getAttribute('data-name');
    document.getElementById('category').value = record.getAttribute('data-category');
    document.getElementById('duration').value = record.getAttribute('data-duration');
    document.getElementById('price').value = record.getAttribute('data-price');
    document.getElementById('description').value = record.getAttribute('data-description');
    document.getElementById('modalTitle').textContent = 'Edit Service';
    document.getElementById('saveServiceBtn').textContent = 'Save Changes';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('serviceModal')).show();
}

function viewService(id)
{
    const record = document.getElementById('service-' + id);
    if (!record) return;
    document.getElementById('viewServiceId').textContent = id;
    document.getElementById('viewServiceName').textContent = record.getAttribute('data-name');
    document.getElementById('viewServiceCategory').textContent = record.getAttribute('data-category');
    document.getElementById('viewServiceDuration').textContent = record.getAttribute('data-duration') + ' minutes';
    document.getElementById('viewServicePrice').textContent = 'PHP ' + Number(record.getAttribute('data-price')).toFixed(2);
    document.getElementById('viewServiceDescription').textContent = record.getAttribute('data-description');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('viewModal')).show();
}

function confirmServiceDelete()
{
    return confirm('Request deletion of this service? Used services cannot be deleted.');
}
