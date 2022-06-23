<?php
defined( 'ABSPATH' ) || die();

$invoices = WLSM_M_Staff_Accountant::get_student_pending_invoices( $student->ID );
?>
<div class="wlsm-content-area wlsm-section-fee-invoices wlsm-student-fee-invoices">
	<div class="wlsm-st-main-title">
		<span>
		<?php esc_html_e( 'Fee Invoices', 'school-management-system' ); ?>
		</span>
	</div>

	<div class="wlsm-st-pending-fee-invoices-section">
	<?php require_once WLSM_PLUGIN_DIR_PATH . 'includes/partials/pending_fee_invoices.php'; ?>
	</div>
</div>
