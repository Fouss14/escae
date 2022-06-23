<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Setting.php';

if ( isset( $from_front ) ) {
	$print_button_classes = 'button btn-sm btn-success';
} else {
	$print_button_classes = 'btn btn-sm btn-success';
}

$due = $invoice->payable - $invoice->paid;
?>

<!-- Print invoice. -->
<div class="d-flex mb-2">
	<div class="col-md-12 wlsm-text-center">
		<br>
		<button type="button" class="<?php echo esc_attr( $print_button_classes ); ?>" id="wlsm-print-invoice-btn" data-styles='["<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/css/bootstrap.min.css' ); ?>","<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/css/wlsm-school-header.css' ); ?>","<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/css/print/wlsm-invoice.css' ); ?>"]' data-title="<?php
		printf(
			/* translators: 1: invoice title, 2: invoice number */
			esc_attr__( 'Fee Invoice - %1$s (%2$s)', 'school-management-system' ),
			esc_attr( WLSM_M_Staff_Accountant::get_invoice_title_text( $invoice->invoice_title ) ),
			esc_attr( $invoice->invoice_number ) );
		?>"><?php esc_html_e( 'Print Fee Invoice', 'school-management-system' ); ?>
		</button>
	</div>
</div>

<!-- Print invoice section. -->
<div class="wlsm" id="wlsm-print-invoice">
	<div class="wlsm-print-invoice-container">

		<?php require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/print/partials/school_header.php'; ?>

		<div class="row">
			<div class="col-md-12">
				<div class="wlsm-h5 wlsm-invoice-heading text-center">
					<?php
					printf(
						wp_kses(
							/* translators: %s: invoice title */
							__( '<span class="wlsm-font-bold">Fee Invoice:</span> %s', 'school-management-system' ),
							array(
								'span' => array( 'class' => array() )
							)
						),
						esc_html( WLSM_M_Staff_Accountant::get_invoice_title_text( $invoice->invoice_title ) )
					);
					?>
					<small class="float-right">
					<?php
					printf(
						wp_kses(
							/* translators: %s: invoice number */
							__( '<span class="wlsm-font-bold">Invoice No.</span> %s', 'school-management-system' ),
							array( 'span' => array( 'class' => array() ) )
						),
						esc_html( $invoice->invoice_number )
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
							<td><?php echo esc_html( WLSM_M_Staff_Accountant::get_invoice_title_text( $invoice->invoice_title ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Invoice Number', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( $invoice->invoice_number ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Date Issued:', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_Config::get_date_text( $invoice->date_issued ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Due Date:', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_Config::get_date_text( $invoice->due_date ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Student Name', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $invoice->student_name ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Enrollment Number', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( $invoice->enrollment_number ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Phone', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Staff_Class::get_phone_text( $invoice->phone ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Email', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $invoice->email ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Class', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Class::get_label_text( $invoice->class_label ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Section', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Class::get_label_text( $invoice->section_label ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Roll Number', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Staff_Class::get_roll_no_text( $invoice->roll_number ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Father Name', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $invoice->father_name ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Amount', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_Config::get_money_text( $invoice->payable ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Discount', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_Config::get_money_text( $invoice->discount ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Payable', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_Config::get_money_text( $invoice->payable ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Paid', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_Config::get_money_text( $invoice->paid ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Due', 'school-management-system' ); ?></th>
							<td><?php echo esc_html( WLSM_Config::get_money_text( $due ) ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Status', 'school-management-system' ); ?></th>
							<td>
							<?php
							echo wp_kses(
								WLSM_M_Invoice::get_status_text( $invoice->status ),
								array( 'span' => array( 'class' => array() ) )
							);
							?>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>

		<?php if ( count( $payments ) ) { ?>
		<div class="row mt-2">
			<div class="col-12">
				<div class="wlsm-h5 wlsm-font-bold wlsm-invoice-sub-heading">
					<?php esc_html_e( 'Payment History', 'school-management-system' ); ?>
				</div>
				<div class="table-responsive w-100">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="text-nowrap"><?php esc_html_e( 'Receipt Number', 'school-management-system' ); ?></th>
								<th class="text-nowrap"><?php esc_html_e( 'Amount', 'school-management-system' ); ?></th>
								<th><?php esc_html_e( 'Payment Method', 'school-management-system' ); ?></th>
								<th><?php esc_html_e( 'Transaction ID', 'school-management-system' ); ?></th>
								<th class="text-nowrap"><?php esc_html_e( 'Date', 'school-management-system' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ( $payments as $row ) {
							?>
							<tr>
								<td class="text-nowrap"><?php echo esc_html( WLSM_M_Invoice::get_receipt_number_text( $row->receipt_number ) ); ?></td>
								<td class="text-nowrap"><?php echo esc_html( WLSM_Config::get_money_text( $row->amount ) ); ?></td>
								<td><?php echo esc_html( WLSM_M_Invoice::get_payment_method_text( $row->payment_method ) ); ?></td>
								<td><?php echo esc_html( WLSM_M_Invoice::get_transaction_id_text( $row->transaction_id ) ); ?></td>
								<td class="text-nowrap"><?php echo esc_html( WLSM_Config::get_date_text( $row->created_at ) ); ?></td>
							</tr>
							<?php
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php } ?>

	</div>
</div>
