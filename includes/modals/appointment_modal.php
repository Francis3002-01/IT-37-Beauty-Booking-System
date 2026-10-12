<?php
$editing = ($form['action'] ?? '') == 'edit';
$showBooking = $openForm || isset($_GET['new']);
$originalBooking = null;
if ($editing && bookingID($form['appointmentID'] ?? '')) {
    $originalRows = bookingQuery(
        $conn,
        'SELECT serviceID,priceAtBooking FROM appointment WHERE appointmentID=?',
        'i',
        [$form['appointmentID']]
    );
    $originalBooking = $originalRows[0] ?? null;
}
?>
<div
    class="modal fade"
    id="appointmentModal"
    tabindex="-1"
    aria-labelledby="appointmentModalTitle"
    aria-hidden="true"
    data-open="<?php echo $showBooking
        ? 'yes'
        : 'no'; ?>"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-3 rounded-4">
            <div class="modal-header border-0">
                <h2 class="modal-title fs-4 fw-bold" id="appointmentModalTitle"><?php echo $editing
                      ? 'Edit Appointment'
                      : 'Create Appointment'; ?></h2>
            </div>
            <div class="modal-body">
                <form
                    id="appointmentForm"
                    method="POST"
                    action="backend%20logic/appointment_backend.php"
                >
                    <?php bookingTokenField(); ?>
                    <input type="hidden" name="action" value="<?php echo $editing
                            ? 'edit'
                            : 'create'; ?>" /><input
                        type="hidden"
                        name="appointmentID"
                        value="<?php echo bookingText(
                            $form['appointmentID'] ?? ''
                        ); ?>"
                    />
                    <?php if ($editing): ?>
                    <input type="hidden" name="clientID" value="<?php echo bookingText(
                            $form['clientID'] ?? ''
                        ); ?>" />
                    <?php endif; ?>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h3 class="fs-6 fw-bold mb-3">Client &amp; Service</h3>
                            <label class="form-label" for="appointmentClient">Client</label>
                            <select
                                id="appointmentClient"
                                name="clientID"
                                class="form-select mb-3"
                                required
                                <?php if (
                                          $editing
                                      ) {
                                          echo 'disabled';
                                      } ?>
                            >
                                <option value="">Select a client</option>
                                <?php foreach (
                                    $clients
                                    as $client
                                ): ?>
                                <option value="<?php echo $client['clientID']; ?>" <?php if (
                                    ($form['clientID'] ?? '') ==
                                    $client['clientID']
                                ) {
                                    echo 'selected';
                                } ?>>
                                    <?php echo bookingText(
                                        $client['firstName'] . ' ' . $client['lastName']
                                    ); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <label class="form-label" for="appointmentService">Service</label>
                            <select
                                id="appointmentService"
                                name="serviceID"
                                class="form-select mb-2"
                                required
                                onchange="suggestAppointmentEnd()"
                            >
                                <option value="">Select a service</option>
                                <?php foreach (
                                          $services
                                          as $service
                                      ): ?>
                                <option
                                    value="<?php echo $service[
                                        'serviceID'
                                    ]; ?>"
                                    data-duration="<?php echo (int) $service['duration']; ?>"
                                    data-price="<?php echo bookingText(
                                        $originalBooking && $originalBooking['serviceID'] == $service['serviceID']
                                            ? $originalBooking['priceAtBooking']
                                            : $service['price']
                                    ); ?>"
                                    <?php if (($form['serviceID'] ?? '') == $service['serviceID']) {
                                        echo 'selected';
                                    } ?>
                                >
                                    <?php echo bookingText($service['serviceName']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (
                                      $editing
                                  ): ?>
                            <div class="form-text mb-3" style="font-size: 0.75rem">
                                Changing the service requires you to recheck beautician
                                availability.
                            </div>
                            <?php endif; ?>
                            <label class="form-label" for="serviceDuration">Duration</label>
                            <input
                                id="serviceDuration"
                                class="form-control bg-light mb-3"
                                type="text"
                                readonly
                                value=""
                            />
                            <label class="form-label" for="appointmentType">Type</label
                            ><select
                                id="appointmentType"
                                name="appointmentType"
                                class="form-select mb-3"
                                onchange="toggleVenueInput()"
                            >
                                <option value="Salon service">Salon service</option>
                                <option value="Home service" <?php if (
                                          ($form['appointmentType'] ?? '') ==
                                          'Home service'
                                      ) {
                                          echo 'selected';
                                      } ?>>Home service</option>
                            </select>
                            <div id="venueWrapper" class="mb-3">
                                <label class="form-label" for="venueInput"
                                    >Location (Required)</label
                                ><textarea
                                    id="venueInput"
                                    name="venue"
                                    class="form-control"
                                    maxlength="255"
                                    rows="2"
                                >
<?php echo bookingText(
          $form['venue'] ?? ''
      ); ?></textarea>
                            </div>
                            <label class="form-label" for="appointmentNotes">Notes (Optional)</label
                            ><textarea
                                id="appointmentNotes"
                                name="notes"
                                class="form-control"
                                maxlength="255"
                                rows="3"
                                placeholder="Special instructions, allergies or directions"
                            >
<?php echo bookingText(
          $form['notes'] ?? ''
      ); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <h3 class="fs-6 fw-bold mb-3">Schedule &amp; Beautician</h3>
                            <label class="form-label" for="appointmentDate">Date</label
                            ><input
                                id="appointmentDate"
                                name="appointmentDate"
                                type="date"
                                class="form-control mb-3"
                                min="<?php echo date(
                                          'Y-m-d'
                                      ); ?>"
                                value="<?php echo bookingText(
                                    $form['appointmentDate'] ?? ''
                                ); ?>"
                                required
                                onchange="invalidateAvailability()"
                            />
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label" for="startTime">Start time</label
                                    ><input
                                        id="startTime"
                                        name="startTime"
                                        type="time"
                                        class="form-control"
                                        value="<?php echo bookingText(
                                                  $form['startTime'] ?? ''
                                              ); ?>"
                                        required
                                        onchange="suggestAppointmentEnd()"
                                    />
                                </div>
                                <div class="col-6">
                                    <label class="form-label" for="endTime">End time</label
                                    ><input
                                        id="endTime"
                                        name="endTime"
                                        type="time"
                                        class="form-control bg-light"
                                        readonly
                                        value="<?php echo bookingText(
                                            $form['endTime'] ?? ''
                                        ); ?>"
                                        required
                                        onchange="invalidateAvailability()"
                                    />
                                </div>
                            </div>
                            <button
                                type="submit"
                                name="check"
                                value="1"
                                formmethod="GET"
                                formaction="appointments.php"
                                formnovalidate
                                class="btn btn-light rounded-pill px-4 mb-3"
                            >
                                Check Availability
                            </button>
                            <label class="form-label d-block" for="appointmentBeautician"
                                >Assign Beautician</label
                            >
                            <select
                                id="appointmentBeautician"
                                name="beauticianID"
                                class="form-select"
                                required
                                <?php if (
                                          !$checked
                                      ) {
                                          echo 'disabled';
                                      } ?>
                            >
                                <option value="">Select an available beautician</option>
                                <?php foreach (
                                    $beauticians
                                    as $person
                                ):
                                    $result =
                                        $availability[$person['beauticianID']] ??
                                        'Check date and time first'; ?>
                                <option value="<?php echo $person[
                                    'beauticianID'
                                ]; ?>" <?php if (($form['beauticianID'] ?? '') == $person['beauticianID']) {
                                    echo 'selected';
                                } ?> <?php if ($result != 'Available') {
                                     echo 'disabled';
                                 } ?>>
                                    <?php echo bookingText(
                                        $person['firstName'] . ' ' . $person['lastName'] . ' (' . $person['beauticianType'] . ')'
                                    ); ?>
                                </option>
                                <?php
                                endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php if (!$editing): ?>
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label for="bookingPaymentMethod" class="form-label"
                                >Mode of Payment</label
                            >
                            <select
                                id="bookingPaymentMethod"
                                name="paymentMethod"
                                class="form-select"
                            >
                                <?php foreach (['Cash', 'Card', 'Online'] as $method): ?>
                                <option <?php if (
                                    ($form['paymentMethod'] ?? 'Cash') ==
                                    $method
                                ) {
                                    echo 'selected';
                                } ?>><?php echo $method; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="bookingAmountPaid" class="form-label"
                                >Amount Paid (PHP)</label
                            >
                            <input
                                id="bookingAmountPaid"
                                oninput="checkPaidAmount(this)"
                                aria-describedby="bookingPaymentHelp"
                                name="amountPaid"
                                class="form-control"
                                type="number"
                                min="0"
                                step="0.01"
                                value="<?php echo bookingText(
                                          $form['amountPaid'] ?? '0'
                                      ); ?>"
                            />
                            <div id="bookingPaymentHelp" class="form-text">
                                Payment can be made after the appointment
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div id="appointmentPrice" class="fw-bold fs-5 text-end mt-4" role="status">
                        Price: PHP 0.00
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3">
                        <button
                            type="button"
                            class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal"
                        >
                            Cancel</button
                        ><button
                            id="saveAppointmentBtn"
                            class="btn btn-dark rounded-pill px-4"
                            <?php if (
                                    !$checked
                                ) {
                                    echo 'disabled';
                                } ?>
                        >
                            <?php echo $editing ? 'Save Changes' : 'Create Appointment'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
