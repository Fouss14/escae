<?php
defined( 'ABSPATH' ) || die();

global $wpdb;

$page_url = WLSM_M_Staff_Accountant::get_invoices_page_url();

$school_id  = $current_school['id'];
$session_id = $current_session['ID'];

$invoice = NULL;

if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
	$id      = absint( $_GET['id'] );
	$invoice = WLSM_M_Staff_Accountant::fetch_invoice( $school_id, $session_id, $id );

	if ( $invoice ) {
		$nonce_action = 'collect-invoice-payment-' . $invoice->ID;

		$student_id              = $invoice->student_id;
		$student_name            = $invoice->student_name;
		$admission_number        = $invoice->admission_number;
		$phone                   = $invoice->phone;
		$email                   = $invoice->email;
		$father_name             = $invoice->father_name;
		$father_phone            = $invoice->father_phone;
		$class_label             = $invoice->class_label;
		$section_label           = $invoice->section_label;
		$invoice_title           = $invoice->invoice_title;
		$invoice_number          = $invoice->invoice_number;
		$invoice_description     = $invoice->invoice_description;
		$invoice_amount          = $invoice->amount;
		$invoice_discount        = $invoice->discount;
		$invoice_date_issued     = $invoice->date_issued;
		$invoice_due_date        = $invoice->due_date;
		$invoice_partial_payment = $invoice->partial_payment;
		$invoice_status          = $invoice->status;
	}
}

if ( ! $invoice ) {
	die();
}

$classes = WLSM_M_Staff_Class::fetch_classes( $school_id );
?>
<div class="row">
	<div class="col-md-12">
		<div class="mt-3 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading-box">
				<span class="wlsm-section-heading">
					<?php
					printf(
						wp_kses(
							/* translators: 1: invoice title, 2: invoice number */
							__( 'Collect Payment For Invoice: %1$s (%2$s)', 'school-management-system' ),
							array(
								'span' => array( 'class' => array() )
							)
						),
						esc_html( $invoice_title ),
						esc_html( $invoice_number )
					);
					?>
				</span>
			</span>
			<span class="float-right">
				<a href="<?php echo esc_url( $page_url ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-file-invoice"></i>&nbsp;
					<?php esc_html_e( 'View All Invoices', 'school-management-system' ); ?>
				</a>
			</span>
		</div>

		<!-- Invoice Detail -->
		<div class="wlsm-form-section">
			<div class="row">
				<div class="col-md-4">
					<div class="wlsm-form-sub-heading wlsm-font-bold">
						<?php esc_html_e( 'Invoice Detail', 'school-management-system' ); ?>
						<a href="<?php echo esc_url( $page_url . '&action=save&id=' . $invoice->ID ); ?>">
							<i class="fas fa-edit"></i>
						</a>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-6">
					<ul class="list-group list-group-flush">
						<li class="list-group-item">
							<span class="wlsm-font-bold"><?php esc_html_e( 'Invoice Title', 'school-management-system' ); ?>:</span>
							<span>
								<?php echo esc_html( WLSM_M_Staff_Accountant::get_invoice_title_text( $invoice_title ) ); ?>
							</span>
						</li>
						<li class="list-group-item">
							<span class="wlsm-font-bold"><?php esc_html_e( 'Amount', 'school-management-system' ); ?>:</span>
							<span><?php echo esc_html( WLSM_Config::get_money_text( $invoice_amount ) ); ?></span>
						</li>
						<li class="list-group-item">
							<span class="wlsm-font-bold"><?php esc_html_e( 'Discount', 'school-management-system' ); ?>:</span>
							<span><?php echo esc_html( WLSM_Config::get_money_text( $invoice_discount ) ); ?></span>
						</li>
						<li class="list-group-item">
							<span class="wlsm-font-bold"><?php esc_html_e( 'Allow Partial Payments', 'school-management-system' ); ?>:</span>
							<span><?php echo esc_html( WLSM_M_Staff_Accountant::get_partial_payments_allowed_text( $invoice_partial_payment ) ); ?></span>
						</li>
					</ul>
				</div>
				<div class="col-md-6">
					<ul class="list-group list-group-flush">
						<li class="list-group-item">
							<span class="wlsm-font-bold"><?php esc_html_e( 'Description', 'school-management-system' ); ?>:</span>
							<span><?php echo esc_html( stripslashes( $invoice_description ) ); ?></span>
						</li>
						<li class="list-group-item">
							<span class="wlsm-font-bold"><?php esc_html_e( 'Date Issued', 'school-management-system' ); ?>:</span>
							<span><?php echo esc_html( WLSM_Config::get_date_text( $invoice_date_issued ) ); ?></span>
						</li>
						<li class="list-group-item">
							<span class="wlsm-font-bold"><?php esc_html_e( 'Due Date', 'school-management-system' ); ?>:</span>
							<span><?php echo esc_html( WLSM_Config::get_date_text( $invoice_due_date ) ); ?></span>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<?php
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/accountant/invoices/invoice_status.php';
		?>

		<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-collect-invoice-payment-form">

			<?php $nonce = wp_create_nonce( $nonce_action ); ?>
			<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

			<input type="hidden" name="action" value="wlsm-collect-invoice-payment">

			<input type="hidden" name="invoice_id" value="<?php echo esc_attr( $invoice->ID ); ?>">

			<?php
			if ( WLSM_M_Invoice::get_paid_key() !== $invoice->status ) {
				require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/accountant/invoices/new_payment.php';
			?>
			<div class="row mt-2 mb-2">
				<div class="col-md-12 text-center">
					<button type="submit" class="btn btn-primary" id="wlsm-collect-invoice-payment-btn" data-message-title="<?php esc_attr_e( 'Confirm Payment!', 'school-management-system' ); ?>" data-message-content="<?php esc_attr_e( 'Are you sure to add this payment?', 'school-management-system' ); ?>">
						<i class="fas fa-plus-square"></i>&nbsp;
						<?php esc_html_e( 'Add New Payment', 'school-management-system' ); ?>
					</button>
				</div>
			</div>
			<?php
			}
			?>

		</form>
		<?php
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/accountant/invoices/invoice_payment_history.php';
		?>
	</div>
</div>
