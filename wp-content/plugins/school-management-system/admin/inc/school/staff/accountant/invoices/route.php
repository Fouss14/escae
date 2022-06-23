<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/global.php';

$action = '';
if ( isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ) {
	$action = sanitize_text_field( $_GET['action'] );
}
?>
<div class="wlsm container-fluid">
	<?php
	if ( in_array( $action, array( 'save', 'collect_payment' ) ) ) {
		$disallow_session_change = true;
	}

	require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/partials/header.php';

	if ( 'save' === $action ) {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/accountant/invoices/save.php';
	} else if ( 'collect_payment' === $action ) {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/accountant/invoices/collect_payment.php';
	} else if ( 'payment_history' === $action ) {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/accountant/invoices/payment_history.php';
	} else {
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/accountant/invoices/index.php';
	}
	?>
</div>
