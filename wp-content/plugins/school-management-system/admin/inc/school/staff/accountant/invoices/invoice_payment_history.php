<?php
defined( 'ABSPATH' ) || die();

$can_delete_payments = WLSM_M_Role::check_permission( array( 'delete_payments' ), $current_school['permissions'] );
?>

<!-- Invoice Payment History -->
<div class="wlsm-form-section wlsm-invoice-payments">
	<div class="row">
		<div class="col-md-12">
			<div class="wlsm-form-sub-heading wlsm-font-bold">
				<?php esc_html_e( 'Payment History', 'school-management-system' ); ?>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<table class="table table-hover table-bordered" id="wlsm-invoice-payments-table" data-invoice="<?php echo esc_attr( $invoice->ID ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'invoice-payments-' . $invoice->ID ) ); ?>">
				<thead>
					<tr class="text-white bg-primary">
						<th><?php esc_html_e( 'Receipt Number', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Amount', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Payment Method', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Transaction ID', 'school-management-system' ); ?></th>
						<th class="text-nowrap"><?php esc_html_e( 'Date', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Note', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Print', 'school-management-system' ); ?></th>
						<?php if ( $can_delete_payments ) { ?>
						<th class="text-nowrap"><?php esc_html_e( 'Delete', 'school-management-system' ); ?></th>
						<?php } ?>
					</tr>
				</thead>
			</table>
		</div>
	</div>
</div>
