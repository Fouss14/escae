<?php
defined( 'ABSPATH' ) || die();

$payment_amount  = '';

$partial_payment_not_allowed = $invoice && ! $invoice_partial_payment;
if ( $partial_payment_not_allowed ) {
	$payment_amount = $due;
}
$collect_payment_methods = WLSM_M_Invoice::collect_payment_methods();
?>

<!-- Collect Payment -->
<div class="wlsm-form-section wlsm-invoice-payments" id="wlsm-collect-payment">
	<?php if ( ! $invoice ) { ?>
	<div class="row">
		<div class="col-md-12">
			<div class="form-group">
				<input class="form-check-input mt-1" type="checkbox" name="collect_invoice_payment" id="wlsm_collect_invoice_payment" value="1">
				<label class="ml-4 mb-1 form-check-label wlsm-font-bold text-dark" for="wlsm_collect_invoice_payment">
					<?php esc_html_e( 'Collect Payment?', 'school-management-system' ); ?>
				</label>
			</div>
			<hr>
		</div>
	</div>
	<div class="wlsm-collect-invoice-payment">
	<?php } ?>
		<div class="row">
			<div class="col-md-12">
				<div class="wlsm-form-sub-heading wlsm-font-bold">
					<?php esc_html_e( 'Add New Payment', 'school-management-system' ); ?>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="col-md-6">
				<div class="form-group">
					<label for="wlsm_payment_amount" class="wlsm-font-bold">
						<?php esc_html_e( 'Amount', 'school-management-system' ); ?>:
					</label>
					<input <?php if ( $partial_payment_not_allowed ) { echo esc_attr('readonly'); } ?> type="number" step="any" min="0" name="payment_amount" class="form-control" id="wlsm_payment_amount" placeholder="<?php esc_attr_e( 'Enter amount', 'school-management-system' ); ?>" value="<?php echo esc_attr( WLSM_Config::sanitize_money( $payment_amount ) ); ?>">
				</div>
				<div class="form-group">
					<label for="wlsm_payment_method" class="wlsm-font-bold">
						<?php esc_html_e( 'Payment Method', 'school-management-system' ); ?>:
					</label>
					<select name="payment_method" class="form-control selectpicker" id="wlsm_payment_method">
						<?php foreach ( $collect_payment_methods as $key => $value ) { ?>
						<option value="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $value ); ?>
						</option>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label for="wlsm_transaction_id" class="wlsm-font-bold">
						<?php esc_html_e( 'Transaction ID', 'school-management-system' ); ?>:
					</label>
					<input type="text" name="transaction_id" class="form-control" id="wlsm_transaction_id" placeholder="<?php esc_attr_e( 'Enter transaction ID', 'school-management-system' ); ?>">
				</div>
				<div class="form-group">
					<label for="wlsm_payment_note" class="wlsm-font-bold">
						<?php esc_html_e( 'Additional Note', 'school-management-system' ); ?>:
					</label>
					<textarea name="payment_note" class="form-control" id="wlsm_payment_note" cols="30" rows="2" placeholder="<?php esc_attr_e( 'Enter additional note', 'school-management-system' ); ?>"></textarea>
				</div>
			</div>
		</div>
	<?php if ( ! $invoice ) { ?>
	</div>
	<?php } ?>
</div>
