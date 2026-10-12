<!-- Read-only appointment details -->
<div
    class="modal fade"
    id="viewAppointment<?php echo $id; ?>"
    tabindex="-1"
    aria-labelledby="viewAppointmentTitle<?php echo $id; ?>"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content p-3 rounded-4">
            <div class="modal-header border-0">
                <h2 id="viewAppointmentTitle<?php echo $id; ?>" class="modal-title fs-5 fw-bold">
                    Appointment Details (#<?php echo $id; ?>)
                </h2>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <?php foreach (
                           [
                               'Client' => $appointment['clientNames'],
                               'Service' => $appointment['serviceName'],
                               'Beautician' => $appointment['beauticianNames'],
                               'Status' => $appointment['appointmentStatus'],
                               'Date' => $appointment['appointmentDate'],
                               'Time' =>
                                   substr($appointment['startTime'], 0, 5) .
                                   '–' .
                                   substr($appointment['endTime'], 0, 5),
                               'Type' => $appointment['appointmentType'],
                               'Location' => $appointment['venue'] ?: 'Salon',
                               'Recorded by' => $appointment['userName'],
                               'Price' => 'PHP ' . number_format($appointment['totalCost'], 2),
                               'Payment Status' => $paymentStatus
                           ]
                           as $label => $value
                       ): ?>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block"><?php echo $label; ?></span
                        ><span class="fw-semibold"><?php echo bookingText(
                            $value
                        ); ?></span>
                    </div>
                    <?php endforeach; ?>
                    <div class="col-12">
                        <span class="text-muted small d-block">Notes</span
                        ><span><?php echo bookingText(
                               $appointment['notes'] ?: '-'
                           ); ?></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button
                    type="button"
                    class="btn btn-dark rounded-pill px-4 w-100"
                    data-bs-dismiss="modal"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Separate payments popup -->
<div
    class="modal fade"
    id="paymentAppointment<?php echo $id; ?>"
    tabindex="-1"
    aria-labelledby="paymentAppointmentTitle<?php echo $id; ?>"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3 rounded-4">
            <div class="modal-header border-0">
                <h2 id="paymentAppointmentTitle<?php echo $id; ?>" class="modal-title fs-5 fw-bold">
                    Payment (#<?php echo $id; ?>)
                </h2>
            </div>
            <div class="modal-body">
                <p><strong>Total:</strong> PHP <?php echo number_format(
                      $appointment['totalCost'],
                      2
                  ); ?></p>
                <p><strong>Paid:</strong> PHP <?php echo number_format(
                    $appointment['paid'],
                    2
                ); ?></p>
                <p><strong>Balance:</strong> PHP <?php echo number_format($balance, 2); ?></p>
                <?php foreach (
                       $payments
                       as $payment
                   ): ?>
                <div class="py-2 small">
                    <strong>PHP <?php echo number_format(
                        $payment['amount'],
                        2
                    ); ?></strong> · <?php echo bookingText(
                        $payment['paymentMethod']
                    ); ?>
                    <div class="text-muted"><?php echo bookingText(
                        $payment['paymentDate']
                    ); ?></div>
                </div>
                <?php endforeach; ?>
                <?php if (!$payments): ?>
                <p class="text-muted">No payments recorded.</p>
                <?php endif; ?>
                <?php if ($balance > 0 && $appointment['appointmentStatus'] != 'Cancelled'): ?>
                <form
                    id="appointmentPaymentForm<?php echo $id; ?>"
                    method="POST"
                    action="backend%20logic/appointment_backend.php"
                    class="pt-3 mt-3"
                >
                    <?php bookingTokenField(); ?><input type="hidden" name="action" value="payment" /><input
                        type="hidden"
                        name="appointmentID"
                        value="<?php echo $id; ?>"
                    />
                    <label class="form-label" for="amount<?php echo $id; ?>">Amount (PHP)</label
                    ><input
                        id="amount<?php echo $id; ?>"
                        class="form-control mb-3"
                        name="amount"
                        oninput="checkPaidAmount(this)"
                        type="number"
                        min="0.01"
                        max="<?php echo $balance; ?>"
                        step="0.01"
                        value="<?php echo $balance; ?>"
                        required
                    />
                    <label class="form-label" for="method<?php echo $id; ?>">Mode of Payment</label
                    ><select
                        id="method<?php echo $id; ?>"
                        name="paymentMethod"
                        class="form-select mb-3"
                    >
                        <option>Cash</option>
                        <option>Card</option>
                        <option>Online</option>
                    </select>
                </form>
                <?php endif; ?>
            </div>
            <div class="modal-footer border-0 justify-content-end">
                <button
                    type="button"
                    class="btn btn-light rounded-pill px-4"
                    data-bs-dismiss="modal"
                >
                    Close
                </button>
                <?php if (
                       $balance > 0 &&
                       $appointment['appointmentStatus'] != 'Cancelled'
                   ): ?><button
                    type="submit"
                    form="appointmentPaymentForm<?php echo $id; ?>"
                    class="btn btn-dark rounded-pill px-4"
                >
                    Save Changes</button
                ><?php endif; ?>
            </div>
        </div>
    </div>
</div>
