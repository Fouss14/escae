<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Invoice.php';

if ( isset( $from_front ) ) {
	$print_button_classes = 'button btn-sm btn-success';
} else {
	$print_button_classes = 'btn btn-sm btn-success';
}
?>

<!-- Print invoice payment. -->
<div class="d-flex mb-2">
	<div class="col-md-12 wlsm-text-center">
		<br>
		<button type="button" class="<?php echo esc_attr( $print_button_classes ); ?>" id="wlsm-print-invoice-payment-btn" data-styles='["<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/css/bootstrap.min.css' ); ?>","<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/css/wlsm-school-header.css' ); ?>","<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/css/print/wlsm-payment.css' ); ?>"]' data-title="
												<?php
												printf(
												/* translators: %s: receipt number */
													esc_attr__( 'Payment Receipt - %s', 'school-management-system' ),
													esc_attr( WLSM_M_Invoice::get_receipt_number_text( $payment->receipt_number ) )
												);
												?>
		"><?php esc_html_e( 'Print Payment Receipt', 'school-management-system' ); ?>
		</button>
	</div>
</div>

<!-- Print invoice payment section. -->
<div class="wlsm" id="wlsm-print-invoice-payment">
	<div class="wlsm-print-invoice-payment-container">

		<?php require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/print/partials/school_header.php'; ?>

		<div class="row">
			<div class="col-md-12">
				<div class="wlsm-h5 wlsm-invoice-payment-heading wlsm-font-bold text-center">
					<?php esc_html_e( 'Payment Receipt', 'school-management-system' ); ?>
					<small class="float-right">
					<?php
					printf(
						wp_kses(
							/* translators: %s: receipt number */
							__( '<span class="wlsm-font-bold">Receipt No.</span> %s', 'school-management-system' ),
							array( 'span' => array( 'class' => array() ) )
						),
						esc_html( WLSM_M_Invoice::get_receipt_number_text( $payment->receipt_number ) )
					);
					?>
					</small>
				</div>
			</div>
		</div>

		<div class="row mt-2">
			<div class="col-12">
				<div class="table-responsive w-100">
					<table class="table table-bordered">
						<tr>
							<th><?php esc_html_e( 'Invoice Title', 'school-management-system' ); ?></th>
							<td>
							<?php
							if ( $payment->invoice_id ) {
								echo esc_html( WLSM_M_Staff_Accountant::get_invoice_title_text( $payment->invoice_title ) );
							} else {
								echo esc_html( WLSM_M_Staff_Accountant::get_invoice_title_text( $payment->invoice_label ) );
							}
							?>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Receipt Number', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Invoice::get_receipt_number_text( $payment->receipt_number ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Amount', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_Config::get_money_text( $payment->amount ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Payment Method', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Invoice::get_payment_method_text( $payment->payment_method ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Transaction ID', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Invoice::get_transaction_id_text( $payment->transaction_id ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Date', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_Config::get_date_text( $payment->created_at ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Student Name', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $payment->student_name ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Enrollment Number', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( $payment->enrollment_number ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Phone', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Staff_Class::get_phone_text( $payment->phone ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Email', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Class::get_label_text( $payment->class_label ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Class', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Class::get_label_text( $payment->class_label ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Section', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Class::get_label_text( $payment->section_label ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Roll Number', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Staff_Class::get_roll_no_text( $payment->roll_number ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Father Name:', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $payment->father_name ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Father Phone:', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Staff_Class::get_phone_text( $payment->father_phone ) ); ?></td>
						</tr>
					</table>
				</div>
			</div>
		</div>

	</div>
</div>
