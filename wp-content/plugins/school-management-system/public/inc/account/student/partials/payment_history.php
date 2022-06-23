<?php
defined( 'ABSPATH' ) || die();

$action_for = 'st';
if ( isset( $change_action ) ) {
	$action_for = $set_action_for;
}

$payments_per_page = 10;

$payments_query = 'SELECT sr.ID as student_id, sr.name as student_name, sr.admission_number, sr.phone, sr.father_name, sr.father_phone, p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.created_at, p.note, p.invoice_label, p.invoice_payable, p.invoice_id, i.label as invoice_title, c.label as class_label, se.label as section_label FROM ' . WLSM_PAYMENTS . ' as p 
	JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id 
	JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id 
	JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id 
	JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id 
	JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id 
	JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id 
	LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id 
	WHERE sr.ID = %d GROUP BY p.ID';

$payments_total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(1) FROM ({$payments_query}) AS combined_table", $student->ID ) );

$payments_page = isset( $_GET['payments_page'] ) ? absint( $_GET['payments_page'] ) : 1;

$payments_page_offset = ( $payments_page * $payments_per_page ) - $payments_per_page;

$payments = $wpdb->get_results( $wpdb->prepare( $payments_query . ' ORDER BY p.ID DESC LIMIT %d, %d', $student->ID, $payments_page_offset, $payments_per_page ) );
?>
<div class="wlsm-content-area wlsm-section-payment-history wlsm-student-payment-history">
	<div class="wlsm-st-main-title">
		<span>
		<?php esc_html_e( 'Payment History', 'school-management-system' ); ?>
		</span>
	</div>

	<div class="wlsm-st-payment-history-section">
		<?php
		if ( count( $payments ) ) {
		?>
		<!-- Student payment history. -->
		<div class="wlsm-table-section">
			<div class="table-responsive w-100 wlsm-w-100">
				<table class="table table-bordered wlsm-student-payment-history-table wlsm-w-100">
					<thead>
						<tr class="bg-primary text-white">
							<th><?php esc_html_e( 'Receipt Number', 'school-management-system' ); ?></th>
							<th><?php esc_html_e( 'Amount', 'school-management-system' ); ?></th>
							<th><?php esc_html_e( 'Payment Method', 'school-management-system' ); ?></th>
							<th><?php esc_html_e( 'Transaction ID', 'school-management-system' ); ?></th>
							<th class="text-nowrap"><?php esc_html_e( 'Date', 'school-management-system' ); ?></th>
							<th><?php esc_html_e( 'Invoice', 'school-management-system' ); ?></th>
							<th><?php esc_html_e( 'Print', 'school-management-system' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $payments as $row ) {
							if ( $row->invoice_id ) {
								$invoice_title = $row->invoice_title;
							} else {
								$invoice_title = $row->invoice_label;
							}
						?>
						<tr>
							<td>
								<?php echo esc_html( WLSM_M_Invoice::get_receipt_number_text( $row->receipt_number ) ); ?>
							</td>
							<td>
								<?php echo esc_html( WLSM_Config::get_money_text( $row->amount ) ); ?>
							</td>
							<td>
								<?php echo esc_html( WLSM_M_Invoice::get_payment_method_text( $row->payment_method ) ); ?>
							</td>
							<td>
								<?php echo esc_html( WLSM_M_Invoice::get_transaction_id_text( $row->transaction_id ) ); ?>
							</td>
							<td>
								<?php echo esc_html( WLSM_Config::get_date_text( $row->created_at ) ); ?>
							</td>
							<td class="text-nowrap">
								<?php echo esc_html( WLSM_M_Staff_Accountant::get_invoice_title_text( $invoice_title ) ); ?>
							</td>
							<td>
								<a class="wlsm-<?php echo esc_attr( $action_for ); ?>-print-invoice-payment wlsm-ml-1" data-invoice-payment="<?php echo esc_attr( $row->ID ); ?>" data-student="<?php echo esc_attr( $row->student_id ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( $action_for . '-print-invoice-payment-' . $row->ID ) ); ?>" href="#" data-message-title="<?php
									printf(
										/* translators: %s: receipt number */
										esc_attr__( 'Payment Receipt - %s', 'school-management-system' ),
										esc_attr( WLSM_M_Invoice::get_receipt_number_text( $row->receipt_number ) ) );
									?>" data-close="<?php echo esc_attr__( 'Close', 'school-management-system' ); ?>">
									<span class="dashicons dashicons-search"></span>
								</a>
							</td>
						</tr>
						<?php
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="wlsm-text-right wlsm-font-medium wlsm-font-bold wlsm-mt-2">
		<?php
		echo paginate_links(
			array(
				'base'      => add_query_arg( 'payments_page', '%#%' ),
				'format'    => '',
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;',
				'total'     => ceil( $payments_total / $payments_per_page ),
				'current'   => $payments_page,
			)
		);
		?>
		</div>

		<?php
		} else {
		?>
		<div class="wlsm-alert wlsm-alert-warning wlsm-font-bold">
			<span class="wlsm-icon wlsm-icon-red">&#33;</span>
			<?php esc_html_e( 'No payment found.', 'school-management-system' ); ?>
		</div>
		<?php
		}
		?>
	</div>
</div>
