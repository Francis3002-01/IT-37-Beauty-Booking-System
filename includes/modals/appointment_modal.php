<?php
require_once __DIR__ . '/../booking.php';

//these values are supplied by appointments.php before including the form
/** @var mysqli $conn */
$form = $form ?? [];
$clients = $clients ?? [];
$services = $services ?? [];
$beauticians = $beauticians ?? [];
$availability = $availability ?? [];
$checked = $checked ?? false;
$openForm = $openForm ?? false;

$editing = ($form['action'] ?? '') == 'edit';
$showBooking = $openForm || isset($_GET['new']);
$originalBooking = null;
$availableBeauticians = [];
if ($checked) {
    foreach ($beauticians as $person) {
        if (($availability[$person['beauticianID']] ?? '') == 'Available') {
            $availableBeauticians[] = $person;
        }
    }
}
$hasAvailableBeauticians = count($availableBeauticians) > 0;


//keep the original booked price when the selected service stays the same
if ($editing && bookingID($form['appointmentID'] ?? '')) {
    $originalRows = bookingQuery(
        $conn,
        'SELECT serviceID, priceAtBooking FROM appointment WHERE appointmentID = ?',
        'i',
        [$form['appointmentID']]
    );
    $originalBooking = $originalRows[0] ?? null;
}
?>
<div class="modal fade" id="appointmentModal" tabindex="-1"
    aria-labelledby="appointmentModalTitle" aria-hidden="true"
    data-open="<?php echo $showBooking ? 'yes' : 'no'; ?>"
    data-no-available="<?php echo isset($_GET['check']) && $checked && !$hasAvailableBeauticians ? 'yes' : 'no'; ?>">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-3 rounded-4">
            <div class="modal-header border-0">
                <h2 class="modal-title fs-4 fw-bold" id="appointmentModalTitle">
                    <?php echo $editing ? 'Edit Appointment' : 'Create Appointment'; ?>
                </h2>
            </div>
            <div class="modal-body">
                <form id="appointmentForm" method="POST" action="backend%20logic/appointment_backend.php">
                    <?php bookingTokenField(); ?>
                    <input type="hidden" name="action" value="<?php echo $editing ? 'edit' : 'create'; ?>">
                    <input type="hidden" name="appointmentID" value="<?php echo bookingText($form['appointmentID'] ?? ''); ?>">
                    <?php if ($editing): ?>
                        <input type="hidden" name="clientID" value="<?php echo bookingText($form['clientID'] ?? ''); ?>">
                    <?php endif; ?>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <h3 class="fs-6 fw-bold mb-3">Client &amp; Service</h3>
                            <label class="form-label" for="appointmentClient">Client</label>
                            <select id="appointmentClient" name="clientID" class="form-select mb-3"
                                required <?php echo $editing ? 'disabled' : ''; ?>>
                                <option value="">Select a client</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?php echo $client['clientID']; ?>"
                                        <?php echo ($form['clientID'] ?? '') == $client['clientID'] ? 'selected' : ''; ?>>
                                        <?php echo bookingText($client['firstName'] . ' ' . $client['lastName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <label class="form-label" for="appointmentService">Service</label>
                            <select id="appointmentService" name="serviceID" class="form-select mb-2"
                                required onchange="suggestAppointmentEnd()">
                                <option value="">Select a service</option>
                                <?php foreach ($services as $service): ?>
                                    <?php
                                    $displayPrice = $originalBooking && $originalBooking['serviceID'] == $service['serviceID']
                                        ? $originalBooking['priceAtBooking'] : $service['price'];
                                    ?>
                                    <option value="<?php echo $service['serviceID']; ?>"
                                        data-duration="<?php echo (int) $service['duration']; ?>"
                                        data-price="<?php echo bookingText($displayPrice); ?>"
                                        <?php echo ($form['serviceID'] ?? '') == $service['serviceID'] ? 'selected' : ''; ?>>
                                        <?php echo bookingText($service['serviceName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($editing): ?>
                                <div class="form-text mb-3" style="font-size: 0.75rem;">
                                    Changing the service requires you to recheck beautician availability.
                                </div>
                            <?php endif; ?>

                            <label class="form-label" for="serviceDuration">Duration</label>
                            <input id="serviceDuration" class="form-control bg-light mb-3" type="text" readonly value="">
                            <label class="form-label" for="appointmentType">Type</label>
                            <select id="appointmentType" name="appointmentType" class="form-select mb-3" onchange="toggleVenueInput()">
                                <option value="Salon service">Salon service</option>
                                <option value="Home service" <?php echo ($form['appointmentType'] ?? '') == 'Home service' ? 'selected' : ''; ?>>
                                    Home service
                                </option>
                            </select>
                            <div id="venueWrapper" class="mb-3">
                                <label class="form-label" for="venueInput">Location (Required)</label>
                                <textarea id="venueInput" name="venue" class="form-control" maxlength="255" rows="2"><?php echo bookingText($form['venue'] ?? ''); ?></textarea>
                            </div>
                            <label class="form-label" for="appointmentNotes">Notes (Optional)</label>
                            <textarea id="appointmentNotes" name="notes" class="form-control" maxlength="255" rows="3"
                                placeholder="Special instructions, allergies or directions"><?php echo bookingText($form['notes'] ?? ''); ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <h3 class="fs-6 fw-bold mb-3">Schedule &amp; Beautician</h3>
                            <label class="form-label" for="appointmentDate">Date</label>
                            <input id="appointmentDate" name="appointmentDate" type="date" class="form-control mb-3"
                                min="<?php echo date('Y-m-d'); ?>" value="<?php echo bookingText($form['appointmentDate'] ?? ''); ?>"
                                required onchange="invalidateAvailability()">
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label" for="startTime">Start time</label>
                                    <input id="startTime" name="startTime" type="time" class="form-control"
                                        value="<?php echo bookingText($form['startTime'] ?? ''); ?>"
                                        required onchange="suggestAppointmentEnd()">
                                </div>
                                <div class="col-6">
                                    <label class="form-label" for="endTime">End time</label>
                                    <input id="endTime" name="endTime" type="time" class="form-control bg-light" readonly
                                        value="<?php echo bookingText($form['endTime'] ?? ''); ?>"
                                        required onchange="invalidateAvailability()">
                                </div>
                            </div>
                            <button type="submit" name="check" value="1" formmethod="GET"
                                formaction="appointments.php" formnovalidate class="btn btn-check-availability rounded-pill px-4 mb-3">
                                Check Availability
                            </button>
                            <label class="form-label d-block" for="appointmentBeautician">Assign Beautician</label>
                            <select id="appointmentBeautician" name="beauticianID" class="form-select"
                                required <?php echo !$checked || !$hasAvailableBeauticians ? 'disabled' : ''; ?>>
                                <option value="">Select a beautician</option>
                                <?php foreach ($availableBeauticians as $person): ?>
                                    <option value="<?php echo $person['beauticianID']; ?>"
                                        <?php echo ($form['beauticianID'] ?? '') == $person['beauticianID'] ? 'selected' : ''; ?>
>
                                        <?php echo bookingText($person['firstName'] . ' ' . $person['lastName'] . ' (' . $person['beauticianType'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text" style="font-size: 0.75rem;">
                                Click Check Availability to load available beauticians.
                            </div>
                        </div>
                    </div>

                    <?php if (!$editing): ?>
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label for="bookingPaymentMethod" class="form-label">Mode of Payment</label>
                                <select id="bookingPaymentMethod" name="paymentMethod" class="form-select">
                                    <?php foreach (['Cash', 'Card', 'Online'] as $method): ?>
                                        <option <?php echo ($form['paymentMethod'] ?? 'Cash') == $method ? 'selected' : ''; ?>>
                                            <?php echo $method; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="bookingAmountPaid" class="form-label">Amount Paid (PHP)</label>
                                <input id="bookingAmountPaid" oninput="checkPaidAmount(this)" aria-describedby="bookingPaymentHelp"
                                    name="amountPaid" class="form-control" type="number" min="0" step="0.01"
                                    value="<?php echo bookingText($form['amountPaid'] ?? '0'); ?>">
                                <div id="bookingPaymentHelp" class="form-text" style="font-size: 0.75rem;">Payment can be made after the appointment</div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div id="appointmentPrice" class="fw-bold fs-5 text-end mt-4" role="status">Price: PHP 0.00</div>
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button id="saveAppointmentBtn" class="btn btn-dark rounded-pill px-4" <?php echo !$checked || !$hasAvailableBeauticians ? 'disabled' : ''; ?>>
                            <?php echo $editing ? 'Save Changes' : 'Create Appointment'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
